<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Models\Ticket;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\User;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketController extends ApiController
{

    protected $policyClass = TicketPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        // if ($this->include('author')) {
        //     return TicketResource::collection(Ticket::with('user')->paginate());
        // }
        // return TicketResource::collection(Ticket::paginate(10));
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
{

        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes()));

        }

        return $this->error('you are not authorized to create this resource', 401);

}


    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);
            if ($this->include('author')) {
            return new TicketResource($ticket->load('user'));
        }

        return new TicketResource($ticket);
        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found', 404);
        }

    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $ticket_id)
    {
        // PATCH

        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('update', $ticket)) {

                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }

            return $this->error('you are not authorized to update this resource', 401);

        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found', 404);
        }
    }

    public function replace(ReplaceTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('replace', $ticket)) {
                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }

            return $this->error('you are not authorized to replace this resource', 401);



        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found', 404);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('delete', $ticket)) {
                $ticket->delete();

                return $this->ok('Ticket successfully deleted');
            }

            return $this->error('you are not authorized to delete this resource', 401);


        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket cannot be found', 404);
        }

    }
}
