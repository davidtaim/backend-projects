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
./task-cli --help # To see the list of available commands

# To add a task
./task-cli add "Do some code"

# To update a task
./task-cli update 1 "Do some code and drin coffee"

# To delete a task
./task-cli delete 1

# To mark a task as [in-progress|done|todo]
./task-cli mark-in-progress 1
./task-cli mark-done 1
./task-cli mark-todo 1

# To list all tasks
./task-cli list
# To list all tasks with an additional filter [done|todo|in-progress]
./task-cli list done
./task-cli list todo
./task-cli list in-progress
```