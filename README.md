kohana-utils
============

Utils which I frequently use in BetaKiller apps.

- [Registry](classes/BetaKiller/Utils/Registry) (trait + class)
- [Factory](classes/BetaKiller/Utils/Factory) (Simple + Cached + Namespaced)
- [Instance](classes/BetaKiller/Utils/Instance) (Simple + Singleton)


Directory `Kohana` contains some useful utils for Kohana-based apps:

- [ORM](classes/BetaKiller/Utils/Kohana/ORM.php) with a lot of helpers
- [Request](classes/BetaKiller/Utils/Kohana/Request.php) (needs to be copied to `application/classes` directory because of Kohana bootstrap algorithm)
- [Response](classes/BetaKiller/Utils/Kohana/Response.php) with helpers for handling AJAX requests + catching exceptions

More needs to be ported from original BetaKiller repo, but I need spare time for it.

Suggestions and PRs are welcome :)

[MIT license](LICENSE)
