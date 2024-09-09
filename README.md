# Due Date Calculator

## Project Overview
This project is a Due Date Calculator developed by Miglécz Sándor as a homework assignment for Emarsys.

## Assignment Details
- **Deadline:** 2024.09.10. 8:30

## Project Description
The Due Date Calculator is a tool designed to calculate the resolution date and time for reported problems. It takes into account working hours and excludes non-working periods.
[Go to homework details](homework.md)

- In terms of not using third-party libraries, I only used PHP 8.1 built-in libraries (DateTimeImmutable), and didn't do low-level date calculations myself, since it's not the focus of the assignment and no framework would expect me to do that in 2024.
- I wrote tests subsequently, I'm not experienced with TDD

## Note
- Against what the homework ordered, I added a CLI just because it eased the development process.

## Installation

1. Ensure you have Docker Desktop installed on your system. This installer will arrange the necessary environment inside the container for the application to run. In case you already have a container called `calculator` running, rename it in the docker-compose.yaml file.

2. Clone the repository:
   ```sh
   git clone git@github.com:sannyer/e.git
   ```

3. Navigate to the project directory:
   ```sh
   cd e
   ```

4. Build and start the container in the background, then open a shell inside the container.
   ```sh
   docker compose up -d
   docker compose exec calculator sh
   ```
   Subsequent commands will be assumed to be run in that shell.

5. Install the dependencies:
   ```sh
   composer install
   ```

6. Make a copy of the example environment file:
   ```sh
   cp .env.example .env
   ```

7. Generate application key:
   ```sh
   php artisan key:generate
   ```

8. The calculator is now ready to use.

## Usage

```sh
php artisan calculator "2024-05-15 10:30:00" "16:00"
php artisan calculator "2024-05-15 10:30:00" 5
```

## Testing

```sh
php artisan test
```
See [test.log](test.log) for the test results.

## Stopping the Docker Container

When you're finished using the calculator, you can stop the Docker container with the following command:

```sh
docker compose down
```

## Technologies Used
- PHP 8.1+
- Laravel 11
- Composer
- PHPUnit

## Contact
Miglécz Sándor

[miglecz.sandor@gmail.com](mailto:miglecz.sandor@gmail.com)

[+3670-317-8994](call:+3670-317-8994)

## Additional Information
For detailed information about the homework assignment, please refer to the [homework details](homework.md).
