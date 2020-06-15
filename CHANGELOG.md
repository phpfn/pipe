# Release Notes

## 1.0.0

- Remove function names normalization
- Add namespaced functions support
- Add namespace auto import

## 0.2.0

- A placeholder char (`_`) replaced by a symbol `Symbol::for(Placeholder::class)`.
- Deleting a call to the `->value` field to avoid conflicts with the 
    `value()` helper function from the `illuminate/support`.
- Function names memoization was added.
- Basic `PipeInterface` was added.
- Class `Pipe` was finalized.
- Added support of `isset` operator, like: `isset(pipe()->function)`.
- Added support for displaying as a string.
- Added an `array` operator support.
- Added a `list` operator support.
- Added a `die` operator support.
- Added a `exit` operator support.
- Added a `empty` operator support.
- Added a `isset` operator support.
- Added a `unset` operator support.
- Added a `eval` operator support.

## 0.1.0

- Basic implementation
