[Chariot router](https://github.com/awesomite/chariot) is a modern and feature rich router that is fast and flexible.

## Installation

```
composer require hiraeth/chariot
```

The `chariot.jin` configuration will be automatically copied to your `config` directory via [opus](https://github.com/imarc/opus).

## Delegates

| Operative Class                           | Operative Intefaces  | Provides
|-------------------------------------------|----------------------|------------------------------------------------------
| `Awesomite\Chariot\Pattern\PatternRouter` | Class Only           | Configuration for the pattern router

## Providers

No providers are included in this package.

## Configuration

```ini
cache_file = writable/cache/routing.cache

[chariot]

	;
	; A group defines a base URL for all routes in this section.  Groups allow you to easily
	; change the base URL for modular functionality.  By default the group is empty so your base
	; URL is effectively /.
	;

	group = ""

	;
	; Patterns allow you to define custom regular expressions for pattern matching in routes which
	; are hinted using an alias.  All patterns should begin with a `:` and the key of the map
	; represents the alias, while the value is the regex to match.  For examples of how patterns
	; are defined and default patterns, see the README at: https://github.com/awesomite/chariot
	;

	patterns = {
		; ":date": "[0-9]{4}-[0-9]{2}-[0-9]{2}"
	}

	;
	; The routes map contains a list of routes whose keys are the route and whose value is
	; another map providing a target and optionally default parameters.  For examples of how
	; routes are defined, see the README at: https://github.com/awesomite/chariot
	;

	routes = {
		; "/": {"target": "HomeAction", "params": { }}
	}
```

The `[chariot]` section is globally recognized, so it can be added to any configuration file in the system to add additional routes or patterns.  Because all routes and patterns are defined on the same router, however, it is possible for these to conflict.  Note that the `cache_file` can only be defined in the `chariot.jin` file itself.
