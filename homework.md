# Due Date Calculator

Thank you for applying to the developer position at Emarsys. This brief exercise is designed to assess your skills in implementing algorithms and writing production-quality code.

## The Problem

We are looking for a solution that implements a due date calculator in an issue-tracking system. Your task is to implement the `CalculateDueDate` method:

- **Input**: Takes the submit date/time and turnaround time.
- **Output**: Returns the date/time when the issue is resolved.

## Rules

- Working hours are from 9AM to 5PM on every working day, Monday to Friday.
- Holidays should be ignored (e.g., A holiday on a Thursday is considered a working day. A working Saturday counts as a non-working day).
- The turnaround time is defined in working hours (e.g., 2 days equal 16 hours). If a problem was reported at 2:12PM on Tuesday and the turnaround time is 16 hours, then it is due by 2:12PM on Thursday.
- A problem can only be reported during working hours (e.g., All submit date values are set between 9AM to 5PM).
- Do not use any third-party libraries for date/time calculations (e.g., Moment.js, Carbon, Joda, etc.) or hidden functionalities of the built-in methods.

## Additional Information

- Use your favorite programming language.
- Do not implement a user interface or CLI.
- Do not write pseudo code. Write code that you would commit/push to a repository and which solves the given problem.
- You have 72 hours to submit your solution.
- You can submit your solution even if you have not finished it fully.

## Bonus (Not Mandatory)

- Including automated tests in your solution is a plus.
- Test-driven (TDD) solutions are especially welcome.
- Clean Code (by Robert C. Martin) principles make us happy.