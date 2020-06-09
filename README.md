# CQRS and Event sourcing lib

**Light and humble php framework exposing classes to ease CQRS and Event-sourcing implementation.**

All of this is experimental and is part of a RD process.

Lot of stuffs have been read, coded and tested, but tons still need to be tried.

This is not bug proof but it can lead to some interesting ways of doing stuffs:

- domain connected

- short, readable and testable
- distributed
- asynchronous

## Commands

#### It's the write side.

- Commands are DTO's that are dispatched  from all over the app to a bus
- They can only affect one aggregate. 

- the bus passes the command to the corresponding CommandHandler, which is a function that handles only this command
- The handler does stuff from command to aggregate: validation, aggregate reconstitution.
- Then it invokes aggregate's domain's method with desired params
- The aggregates raise domain events, that are stored in the event store.
- Command Handlers usually don't return anything (except ack/nack if needed)
- Commands are usually handled synchronously

## Events

- they are DTO's that are dispatched by aggregate, based on it's own domain methods

- they are appended (only) in event store
- when reconstituting an aggregate, all the events related to this aggregate are loaded in an event stream
- iterating over the stream, all the aggregate's appliers methods (in general `applyEventName(EventName $eventName)`) are called and execute domain logic on aggregate
- at the the aggregate is in its final state.

#### Publishing

When an event is recorded in event store, a tracker worker broadcast the news of its arrival to every connected consumers.
This is done through messenging: rmq, redis, mysql, whatever implementation that fits your needs.

**The tracker is just an independant worker whose job is only to check for the last events recorded and publish the news for everybody in the message broker.**

Nothing else: no domain logic or whatever

#### Consuming

Now that the DomainEvents are published, we need, (and all interested connected app need) a worker that consume those events.
As for the commands, a consumer

- Gets the events from the queue (FIFO)

- retrieves the corresponding EventHandler

- passes him the event

In the event handler, you can do whatever side effects needed for the application.
**En event is something that has occured**
So the actions in the event handlers don't need to validate anything: the command handler and the aggregate have already done it.

As side effects, in an event handler we usually:

- call a projection script whose job is to create or update readmodels

- notify users that somethind has happened

- sync stuffs with other db's

- etc.

## Logs
As a lot of stuffs are handled asynchronously and as part of batches processes, it's important to keep an eye open on logs.

They should be diluted everywhere in the domain code.