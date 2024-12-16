# About this project

This is a fork of `readdle/stripe-httpclient-mock`. Kudos to them for most of the hard work!

This is pretty raw library which is aimed to provide full HTTP client mock for `stripe-php` library in order to
perform tests without actually sending HTTP requests to Stripe.

At the moment this library covers a small part of `stripe-php` functionality but there is a hope that one day
it will be developed enough to cover everything `stripe-php` provides.

The main idea behind this library is to provide stateful "server" which remembers of what he was asked before.
The state is not saved between lifecycles but within a single lifecycle the state is preserved. This is the
main difference from [the official Stripe's server mock](https://github.com/stripe/stripe-mock).
Another reason for this library to exist is to avoid having a separate component of you application written
in a different programming language and thus requiring to have another container with it (or environment
configuration in container-less case).

# Taking a part in this project

The task "to cover all the functionality of `stripe-php` library" is really huge. It's possible to achieve it,
but it requires a huge efforts (as huge as the task is) and is not always possible while you are working on the
main project.

Therefore, it is **highly** appreciated to take part in this project for anyone interested in any of thees ways:
- You have an idea on how to improve this project, or you see that something is wrong? Don't hesitate opening
an issue.
- You have time and will to add/improve functionality or fix a bug? Your pull requests would be **extremely**
valuable

If you cover just a part of Stripe's API you're using, it is possible that one day the whole API will be covered.

# Installation

Nothing special here, just use composer to install the package:

> composer install readdle/stripe-httpclient-mock

# Usage

Usage is as simple as two lines of code in bootstrap script of your PHPUnit configuration:

>\Readdle\StripeHttpClientMock\HttpClient::$apiKey = "your_api_key_goes_here";
> 
>\Stripe\ApiRequestor\ApiRequestor::setHttpClient(new Readdle\StripeHttpClientMock\HttpClient());

That's it, now you have your instance of `stripe-php`'s HTTP client mocked, and it will "communicate" with a
piece of code instead of performing real HTTP requests.

# Overall structure

### Files

`src/Collection.php` - representation of collection of entities

`src/EntityManager.php` - manager, which is responsible for creating/updating/deleting/listing entities, also performs 
search and paging functions

`src/HttpClient.php` - HTTP client which substitutes `stripe-php`'s curl-based client

### Directories

`src/Entity` - implemented entities, each entity **must** extend AbstractEntity class

`src/Error` - erroneous responses, each error **must** extend AbstractError class

`src/Success` - success responses, which doesn't contain entity in it, but an information about an action
and its result, **must** implement `ResponseInterface`

# Entity class structure

### Properties

Each entity **must** have at least `$props` property filled in with all the fields this entity has (the list of fields
for each entity could be found in [Stripe's documentation](https://stripe.com/docs/api)).

### Prefix

In most cases an entity will override method `prefix()` in order to have the correct prefix in newly generated IDs.

---

The following is optional and should be applied in cases when it is needed, however to have **all** the functionality
covered it's better to follow these instructions where it's applicable.

---

### Creation of an entity

If the creation of an entity requires any additional actions, you can override `create()` method and do something
prior/after the creation happens. Usually it's useful for creating related entities.

### Expandable properties

Entity may have property `$expandableProps` filled with the fields which could be expanded (they are marked
as "expandable" in the documentation). By default, if a field is listed in this array and request contains
`expand` parameter which requires this field to be expanded, an entity will try to expand this field automatically.
The field name will be used as sub-entity name (class) and its value will be used as an ID. This is a recursive
action, so in case when `expand` parameter requires expanding of a field in the expanded field, it will be passed
to subsequent entity and will be expanded in the same way.

In case when the logic of expanding the field is more complex, you can override `howToExpand()` method and
implement specific piece of logic for the entity there. Please, don't forget to call parent method for cases
when your implementation is not covering the case. At the moment there are two possible types of array which
this method should return:

> ['target' => 'expandableProp', 'object' => 'entity_name', 'params' => []]

This one tells the library to find an entity with `entity_name` using main object's field value as its ID.
Params would be passed to `retreiveEntity()`, so additional filtering is possible.

> ['target' => 'objectSearcher', 'object' => 'entity_name', 'searcher' => function ($mainEntity, $entities) {} ]

This one tells the library that it should pass the main entity (the one which field is being expanded) and the
whole collection of entities of `entity_name` class to the searcher function in order for it to find appropriate
entity itself.

### Sub-actions

Entity may have property `$subActions` filled with all the available sub-actions. Sub-action is an action performed on
an entity which is not following (due to impossibility) the REST concept. For example:
> POST /v1/payment_methods/:id/attach
> 
> POST /v1/payment_methods/:id/detach

These are not actions of creating/updating/deleting, but performing additional action on the exact entity.

`$subActions` is an associative array where the key is an action (what is stated in the request) and the value is
a name of a method, which is responsible for performing this action. This method should return an implementation
of `ResponseInterface` and it will be returned as an "API response" to the `stripe-php` client.

In case when this functionality can't fulfil an entity's behaviour, you can override `subAction()` method and implement
custom logic for that entity. Don't forget to call parent method in order to cover regular cases.

### Sub-entities

Entity may have property `$subEntities` filled with entity names of its sub-entities. Sub-entity is an entity
which is accessed through the main entity, take a look at these examples:
>  POST /v1/customers/:id/tax_ids
> 
> GET /v1/customers/:id/tax_ids/:id
> 
> DELETE /v1/customers/:id/tax_ids/:id
> 
> GET /v1/customers/:id/tax_ids
> 
Despite the fact that these requests are performed (following the REST concept) on the `customer` entity, actually
all of them are performed on the `tax_id` entity of the customer with the specified ID. So, the library transforms
all these actions in a way like they are performed (in this particular example) on the `tax_id` entity, but using
`customer`'s ID as a filter (or a value for the appropriate field in case when the `tax_id` entity is being created).

### Uncovered requests

It is possible (and even likely) that there are some specific requests which are not covered by this library.
If the requested URL differs from a regular one (performing an action on an entity, performing a sub-action on an
entity or performing an action on sub-entity), you can override `parseUrlTail()` method of the appropriate entity and
add logic which parses this specific request. But it's most likely that the library won't be able to deal with
this result, so there are two preferred ways of sorting it out:
- open an issue and ask for the functionality that the library lacks with detailed description of how to implement it
- improve the library and create a pull request with code which covers this specific case

Both options would be **highly** appreciated and the reaction will follow as soon as it possible.


#### Testing

To run the tests, just execute:

`vendor/bin/phpunit  -c tests/phpunit.xml`
