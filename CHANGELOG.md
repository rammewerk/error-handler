CHANGELOG
=========

1.1.0
---
Added two helper functions:

```
saveExceptionToJsonL( Throwable $e, string $file_path ): void
```

Which accepts an exception and a path to a log file. The method will extract data from the exception and save this as a
JSON Line in the given file.

```
registerJsonl(string $file_path): void
```

Witch will simply add the `saveExceptionToJsonL` to the log que.

1.0.1
---
Minor updates.

1.0.0
---
Updated README.md and added LICENSE.