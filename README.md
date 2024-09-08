# Due Date Calculator

## Project Overview
This project is a Due Date Calculator developed by Miglécz Sándor as a homework assignment for Emarsys.

## Assignment Details
- **Assignee:** Miglécz Sándor
- **Company:** Emarsys
- **Project:** Due Date Calculator
- **Deadline:** 2024.09.10. 8:30

## Project Description
The Due Date Calculator is a tool designed to calculate the resolution date and time for reported problems. It takes into account working hours and excludes non-working periods.
[Link to homework details](homework.md)

- In terms of not using third-party libraries, I only used PHP 8.1 built-in libraries (DateTimeImmutable), and didn't do low-level date calculations myself, since it's not the focus of the assignment and no framework would expect me to do that in 2024.
- I wrote tests subsequently, I'm not experienced with TDD

## Features
- Calculate due dates based on report submission time
- Consider working hours (9AM to 5PM, Monday to Friday)
- Exclude non-working hours and days from calculations

## Installation

1. Ensure you have PHP 8.1 or higher and composer installed.

2. Clone the repository:
   ```
   git clone https://github.com/sannyer/e.git
   ```

3. Navigate to the project directory:
   ```
   cd e
   ```

4. Install the dependencies:
   ```
   composer install
   ```

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. The calculator is now ready to use.

## Usage

```
php artisan calculator "2024-05-15 10:30:00" "16:00"
```

## Testing

```
php artisan test
```

## Technologies Used
- PHP 8.1+
- Laravel 11
- Composer
- PHPUnit

## Contributing
This is a solo project, so no contributions are accepted.

## License
This project is licensed under the MIT License.

## Contact
Miglécz Sándor
[miglecz.sandor@gmail.com](mailto:miglecz.sandor@gmail.com)

## Additional Information
For detailed information about the homework assignment, please refer to the [homework details](homework.md).
