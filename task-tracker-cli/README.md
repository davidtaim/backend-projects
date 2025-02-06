# Task Tracker CLI

Sample solution for the [task-tracker](https://roadmap.sh/projects/task-tracker) challenge from [roadmap.sh](https://roadmap.sh/).

## How to run

Clone the repository:

```bash
git clone https://github.com/davidtaim/task-tracker-cli
cd task-tracker-cli
```

Run the following command to run the project (php 8.3 or above, should be installed):

```bash
php task-cli.php --help # To see the list of available commands

# To add a task
php task-cli.php add "Do some code"

# To update a task
php task-cli.php update 1 "Do some code and drin coffee"

# To delete a task
php task-cli.php delete 1

# To mark a task as [in-progress|done|todo]
php task-cli.php mark-in-progress 1
php task-cli.php mark-done 1
php task-cli.php mark-todo 1

# To list all tasks
php task-cli.php list
# To list all tasks with an additional filter [done|todo|in-progress]
php task-cli.php list done
php task-cli.php list todo
php task-cli.php list in-progress
```