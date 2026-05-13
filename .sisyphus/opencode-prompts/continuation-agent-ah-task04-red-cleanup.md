# Continuation Agent AH - Task 04 RED Evidence Cleanup

You are not alone in this repository. Other agents may be working in parallel. Do not revert or overwrite unrelated changes. Do not commit or push.

Work in `/Users/macbook/Herd/ariops`. Prefix shell commands with `rtk`.

## Goal

Reduce the evidence-index gap for `task-04-red.log` / Planning RED trace without changing planning behavior.

## Scope You Own

- `.sisyphus/evidence/task-04-red.log`
- `.sisyphus/evidence/task-04-tdd-trace.md`
- `.sisyphus/evidence/task-04-red-cleanup-continuation.md`

## Must Not Touch

- Application code
- Tests
- Mobile/React files
- Release risk log or agent board

## Required Workflow

1. Read Planning tests and existing Task 4 evidence.
2. Determine whether a historical RED log can be reconstructed from current test names/evidence without fabricating command output.
3. If reconstructing, clearly label it as "reconstructed RED evidence" and list test names that would fail before implementation.
4. Do not invent historical console output.
5. Write/update the scoped evidence files only.
6. Run `rtk rg "task-04-red|Planning TDD RED|RED" .sisyphus/evidence/evidence-index.md .sisyphus/evidence/task-04-tdd-trace.md .sisyphus/evidence/task-04-red.log`.

## Output

Final response must list changed files and whether the gap can be considered reduced or still partial.
