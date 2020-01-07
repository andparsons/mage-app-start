<?php

namespace Magento\Mtf\System\Observer;

use Magento\Mtf\System\Event\Event;

/**
 * AllureWebapiResponse observer.
 */
class AllureWebapiResponse extends AbstractAllureObserver
{
    /**
     * Collect response source artifact to storage.
     *
     * @param Event $event
     * @return void
     */
    public function process(Event $event)
    {
        $this->addAttachment(
            json_encode($event->getSubjects()[0]),
            'webapi-response-' . $event->getFileIdentifier(),
            'text/json'
        );
    }
}
