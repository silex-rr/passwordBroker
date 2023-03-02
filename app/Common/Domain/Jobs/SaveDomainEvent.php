<?php

namespace App\Common\Domain\Jobs;

use App\Common\Domain\Events\DomainEvent;
use App\Common\Domain\Traits\Saveable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SaveDomainEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Saveable;

    private Model $entity;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private DomainEvent $event)
    {
        if (property_exists($event, 'entity')) {
            $this->entity = $event->entity;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() //DomainEventRepository $eventRepository
    {
//        return $eventRepository->createFromData([
//            'event_id' => Uuid::uuid4()->toString(),
//            'event_body' => json_encode(array_filter(array_except(
//                    get_object_vars($this->event), ['entity'])
//            )),
//            'eventable_type' => $this->entity ?
//                get_class($this->entity) : null,
//            'eventable_id' => $this->entity ?
//                $this->entity->getKey() : null,
//            'event_type' => $this->event->getName(),
//            'user_id' => $this->event->user ?
//                $this->event->user->getKey() : null
//        ]);


        //Id ED7BA470-8E54-465E-825C-99712043E01C
        //event_body {"id":91977,"fname":"Jesse","lname":"Griffin",
        //"role_id":3,"address":"3230 Sweetwater Springs
        //Blvd.","city":"Spring Valley","zip":"91977",
        //"state":"CA","created_at":"2020-01-20 16:20:00",
        //"updated_at":"2020-01-20 16:20:20"}
        //eventable_type Claim\Submission\Domain\Models\Claim
        //eventable_id 9140202
        //user_id 426
        //event_type Claim\Submission\Domain\Events\ClaimWasSubmitted
    }
}
