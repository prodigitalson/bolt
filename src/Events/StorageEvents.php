<?php
namespace Bolt\Events;

/**
 * Definitions for all possible StorageEvents.
 *
 *  * @codeCoverageIgnore
 */
final class StorageEvents
{
    private function __construct()
    {
    }

    // we make no distinction between insert/update
    const PRE_SAVE      = 'preSave';
    const POST_SAVE     = 'postSave';
    
    const PRE_INSERT    = 'preInsert';
    const POST_INSERT   = 'postInsert';

    const PRE_DELETE    = 'preDelete';
    const POST_DELETE   = 'postDelete';
    
    const PRE_HYDRATE   = 'preHydrate';
    const POST_HYDRATE  = 'postHydrate';
}
