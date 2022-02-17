<?php

namespace ZeroDaHero\LaravelWorkflow\Events;

use Serializable;
use Symfony\Component\Workflow\Event\Event;
use Workflow;

/**
 * @method Marking getMarking()
 * @method object getSubject()
 * @method Transition getTransition()
 * @method WorkflowInterface getWorkflow()
 * @method string getWorkflowName()
 * @method mixed getMetadata(string $key, $subject)
 */
abstract class BaseEvent extends Event implements Serializable
{
    public function __serialize(): array
    {
        return [
            'base_event_class' => get_class($this),
            'subject' => $this->getSubject(),
            'marking' => $this->getMarking(),
            'transition' => $this->getTransition(),
            'workflow' => [
                'name' => $this->getWorkflowName(),
            ],
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->subject = $data['subject'];
        $this->marking = $data['marking'];
        $this->transition = $data['transition'] ?? null;
        $workflowName = $data['workflow']['name'] ?? null;
        $this->workflow = Workflow::get($this->subject, $workflowName);
    }

    public function serialize()
    {
        return serialize($this->__serialize());
    }

    public function unserialize($serialized)
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * Creates a new instance from the base Symfony event
     */
    public static function newFromBase(Event $symfonyEvent)
    {
        return new static(
            $symfonyEvent->getSubject(),
            $symfonyEvent->getMarking(),
            $symfonyEvent->getTransition(),
            $symfonyEvent->getWorkflow(),
            $symfonyEvent->getContext()
        );
    }
}
