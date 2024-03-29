<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\services;

use Craft;
use craft\base\ElementInterface;
use craft\behaviors\RevisionBehavior;
use craft\db\Query;
use craft\db\Table;
use craft\errors\InvalidElementException;
use craft\events\RevisionEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\Queue;
use craft\queue\jobs\PruneRevisions;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidArgumentException;

/**
 * Revisions service.
 *
 * An instance of the service is available via [[\craft\base\ApplicationTrait::getRevisions()|`Craft::$app->revisions`]].
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.2.0
 */
class Revisions extends Component
{
    /**
     * @event RevisionEvent The event that is triggered before a revision is created.
     */
    const EVENT_BEFORE_CREATE_REVISION = 'beforeCreateRevision';

    /**
     * @event RevisionEvent The event that is triggered after a revision is created.
     */
    const EVENT_AFTER_CREATE_REVISION = 'afterCreateRevision';

    /**
     * @event RevisionEvent The event that is triggered before an element is reverted to a revision.
     */
    const EVENT_BEFORE_REVERT_TO_REVISION = 'beforeRevertToRevision';

    /**
     * @event RevisionEvent The event that is triggered after an element is reverted to a revision.
     */
    const EVENT_AFTER_REVERT_TO_REVISION = 'afterRevertToRevision';

    /**
     * Creates a new revision for the given element.
     *
     * If the element appears to have not changed since its last revision, its last revision will be returned instead.
     *
     * @param ElementInterface $source The element to create a revision for
     * @param int|null $creatorId The user ID that the revision should be attributed to
     * @param string|null $notes The revision notes
     * @param array $newAttributes any attributes to apply to the draft
     * @param bool $force Whether to force a new revision even if the element doesn't appear to have changed since the last revision
     * @return ElementInterface The new revision
     * @throws \Throwable
     */
    public function createRevision(ElementInterface $source, int $creatorId = null, string $notes = null, array $newAttributes = [], bool $force = false): ElementInterface
    {
        // Make sure the source isn't a draft or revision
        if ($source->getIsDraft() || $source->getIsRevision()) {
            throw new InvalidArgumentException('Cannot create a revision from another revision or draft.');
        }

        $lockKey = 'revision:' . $source->id;
        $mutex = Craft::$app->getMutex();
        if (!$mutex->acquire($lockKey, 3)) {
            throw new Exception('Could not acquire a lock to save a revision for element ' . $source->id);
        }

        $db = Craft::$app->getDb();

        $num = ArrayHelper::remove($newAttributes, 'revisionNum');

        if (!$force || !$num) {
            // Find the source's last revision number, if it has one
            $lastRevisionNum = $db->usePrimary(function() use ($source) {
                return (new Query())
                    ->select(['num'])
                    ->from([Table::REVISIONS])
                    ->where(['sourceId' => $source->id])
                    ->orderBy(['num' => SORT_DESC])
                    ->limit(1)
                    ->scalar();
            });

            if (!$force && $lastRevisionNum) {
                // Get the revision, if it exists for the source's site
                /** @var ElementInterface|RevisionBehavior|null $lastRevision */
                $lastRevision = $db->usePrimary(function() use ($source, $lastRevisionNum) {
                    return $source::find()
                        ->revisionOf($source)
                        ->siteId($source->siteId)
                        ->anyStatus()
                        ->andWhere(['revisions.num' => $lastRevisionNum])
                        ->one();
                });

                // If the source hasn't been updated since the revision's creation date,
                // there's no need to create a new one
                if ($lastRevision && $source->dateUpdated->getTimestamp() === $lastRevision->dateCreated->getTimestamp()) {
                    $mutex->release($lockKey);
                    return $lastRevision;
                }
            }

            // Get the next revision number for this element
            if ($lastRevisionNum) {
                $num = $lastRevisionNum + 1;
            } else {
                $num = 1;
            }
        }

        if ($creatorId === null) {
            // Default to the logged-in user ID if there is one
            $creatorId = Craft::$app->getUser()->getId();
        }

        // Fire a 'beforeCreateRevision' event
        $event = new RevisionEvent([
            'source' => $source,
            'creatorId' => $creatorId,
            'revisionNum' => $num,
            'revisionNotes' => $notes,
        ]);
        $this->trigger(self::EVENT_BEFORE_CREATE_REVISION, $event);
        $notes = $event->revisionNotes;
        $creatorId = $event->creatorId;
        $source = $event->source;

        $elementsService = Craft::$app->getElements();

        $transaction = $db->beginTransaction();
        try {
            // Create the revision row
            Db::insert(Table::REVISIONS, [
                'sourceId' => $source->id, // todo: remove in v4
                'creatorId' => $creatorId,
                'num' => $num,
                'notes' => $notes,
            ], false);

            // Duplicate the element
            $newAttributes['canonicalId'] = $source->id;
            $newAttributes['revisionId'] = $db->getLastInsertID(Table::REVISIONS);
            $newAttributes['behaviors']['revision'] = [
                'class' => RevisionBehavior::class,
                'creatorId' => $creatorId,
                'revisionNum' => $num,
                'revisionNotes' => $notes,
            ];

            if (!isset($newAttributes['dateCreated'])) {
                $newAttributes['dateCreated'] = $source->dateUpdated;
            }

            $revision = $elementsService->duplicateElement($source, $newAttributes);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $mutex->release($lockKey);
            throw $e;
        }

        // Fire an 'afterCreateRevision' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_CREATE_REVISION)) {
            $this->trigger(self::EVENT_AFTER_CREATE_REVISION, new RevisionEvent([
                'source' => $source,
                'creatorId' => $creatorId,
                'revisionNum' => $num,
                'revisionNotes' => $notes,
                'revision' => $revision,
            ]));
        }

        $mutex->release($lockKey);

        // Prune any excess revisions
        if (Craft::$app->getConfig()->getGeneral()->maxRevisions) {
            Queue::push(new PruneRevisions([
                'elementType' => get_class($source),
                'sourceId' => $source->id,
                'siteId' => $source->siteId,
            ]), 2049);
        }

        return $revision;
    }

    /**
     * Reverts an element to a revision, and creates a new revision for the element.
     *
     * @param ElementInterface $revision The revision whose source element should be reverted to
     * @param int $creatorId The user ID that the new revision should be attributed to
     * @return ElementInterface The new source element
     * @throws InvalidElementException
     * @throws \Throwable
     */
    public function revertToRevision(ElementInterface $revision, int $creatorId): ElementInterface
    {
        /** @var ElementInterface|RevisionBehavior $revision */
        $canonical = $revision->getCanonical();

        // Fire a 'beforeRevertToRevision' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_REVERT_TO_REVISION)) {
            $this->trigger(self::EVENT_BEFORE_REVERT_TO_REVISION, new RevisionEvent([
                'source' => $canonical,
                'creatorId' => $creatorId,
                'revisionNum' => $revision->revisionNum,
                'revisionNotes' => $revision->revisionNotes,
                'revision' => $revision,
            ]));
        }

        // "Duplicate" the revision with the source element's ID, UID, and content ID
        $newSource = Craft::$app->getElements()->updateCanonicalElement($revision, [
            'revisionCreatorId' => $creatorId,
            'revisionNotes' => Craft::t('app', 'Reverted content from revision {num}.', ['num' => $revision->revisionNum]),
        ]);

        // Fire an 'afterRevertToRevision' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_REVERT_TO_REVISION)) {
            $this->trigger(self::EVENT_AFTER_REVERT_TO_REVISION, new RevisionEvent([
                'source' => $newSource,
                'creatorId' => $creatorId,
                'revisionNum' => $revision->revisionNum,
                'revisionNotes' => $revision->revisionNotes,
                'revision' => $revision,
            ]));
        }

        return $newSource;
    }
}
