<?php

declare(strict_types=1);

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in-progress';
    case DONE = 'done';
}

class Task
{
    public function __construct(
        public string $id,
        public string $description,
        public TaskStatus $status,
        public string $createdAt,
        public string $updatedAt
    ) {
    }
}

enum TaskCliActions: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case MARK_IN_PROGRESS = 'mark-in-progress';
    case MARK_DONE = 'mark-done';
    case MARK_TODO = 'mark-todo';
    case LIST = 'list';
    case HELP = '--help';
}

class TaskCli
{
    private const string FILE = 'tasks.json';

    /**
     * @var Task[]
     */
    private array $tasks;

    public function __construct()
    {
        $this->tasks = $this->getOrCreate();
    }

    public function run(array $argv): void
    {
        $commands = array_map(fn($case) => $case->value, TaskCliActions::cases());

        if (count($argv) <= 0 || !in_array($argv[0], $commands)) {
            print "./task-cli --help to see available commands" . PHP_EOL;
            return;
        }

        $result = match ($argv[0]) {
            TaskCliActions::HELP->value => $this->printHelp(),
            TaskCliActions::CREATE->value => $this->create($argv),
            TaskCliActions::UPDATE->value => $this->update($argv),
            TaskCliActions::DELETE->value => $this->delete($argv),
            TaskCliActions::MARK_IN_PROGRESS->value => $this->changeStatus($argv, TaskStatus::IN_PROGRESS),
            TaskCliActions::MARK_DONE->value => $this->changeStatus($argv, TaskStatus::DONE),
            TaskCliActions::MARK_TODO->value => $this->changeStatus($argv, TaskStatus::TODO),
            TaskCliActions::LIST ->value => $this->listTasks($argv),
            default => $this->printHelp()
        };

        print $result;

    }

    private function printHelp(): string
    {
        $result = "./task-cli create [task]\n";
        $result .= "./task-cli update [id] [task]\n";
        $result .= "./task-cli delete [id]\n";
        $result .= "./task-cli mark-in-progress [id]\n";
        $result .= "./task-cli mark-done [id]\n";
        $result .= "./task-cli mark-todo [id]\n";
        $result .= "./task-cli list [todo|in-progress|done]\n";
        $result .= "./taskcli --help to see this.\n";
        return $result;
    }

    private function listTasks(array $argv): string
    {
        $count = count($argv);
        switch ($count) {
            case 1:
                if (!count($this->tasks)) {
                    return "There is not any tasks added!\n";
                }
                return $this->printTasks($this->tasks);
            case 2:
                $status = array_map(fn($status) => $status->value, TaskStatus::cases());
                if (!in_array($argv[1], $status)) {
                    return "./task-cli list [todo|in-progress|done]\n";
                }
                $tasks = array_filter($this->tasks, fn($task) => $task->status->value === $argv[1]);
                if (count($tasks) <= 0) {
                    return "There is not any tasks with this filter: {$argv[1]}\n";
                }
                return $this->printTasks($tasks);
            default:
                return "./task-cli list [todo|in-progress|done]\n";
        }
    }

    /**
     * @param Task[] $tasks
     */
    private function printTasks(array $tasks): string
    {
        $headers = ['ID', 'DESCRIPTION', 'STATUS', 'CREATED AT', 'UPDATED AT'];

        $maxLen = [4, 13, 8, 12, 12];
        foreach ($tasks as $task) {
            if ($maxLen[0] - 2 < strlen($task->id))
                $maxLen[0] = strlen($task->id) + 2;

            if ($maxLen[1] - 2 < strlen($task->description))
                $maxLen[1] = strlen($task->description) + 2;

            if ($maxLen[2] - 2 < strlen($task->status->value))
                $maxLen[2] = strlen($task->status->value) + 2;

            if ($maxLen[3] - 2 < strlen($task->createdAt))
                $maxLen[3] = strlen($task->createdAt) + 2;

            if ($maxLen[4] - 2 < strlen($task->updatedAt))
                $maxLen[4] = strlen($task->updatedAt) + 2;
        }

        foreach ($headers as $i => $header) {
            $this->printTaskCol($maxLen[$i], $header);
        }

        print "\n";

        foreach ($tasks as $task) {
            $this->printTaskCol($maxLen[0], $task->id);
            $this->printTaskCol($maxLen[1], $task->description);
            $this->printTaskCol($maxLen[2], $task->status->value);
            $this->printTaskCol($maxLen[3], $task->createdAt);
            $this->printTaskCol($maxLen[4], $task->updatedAt);
            print "\n";
        }

        return "\n";
    }

    private function printTaskCol(int $maxLen, string $value)
    {
        print $value;
        for ($i = 0; $i < $maxLen - strlen($value); $i++) {
            print " ";
        }
    }

    private function changeStatus(array $argv, TaskStatus $status): string
    {
        $count = count($argv);
        if ($count > 2 || $count < 2) {
            return "./task-cli {$argv[0]} [id]\n";
        }

        $id = $argv[1];
        $ids = array_filter($this->tasks, fn($task) => $task->id === $id);
        if ($ids) {
            $this->tasks[array_keys($ids)[0]]->status = $status;
            $this->saveTasks();
        } else {
            return "Task not found with ID: $id\n";
        }

        return "Task updated succesfully (ID: $id)\n";
    }

    private function delete(array $argv): string
    {
        $count = count($argv);
        if ($count > 2 || $count < 2) {
            return "./task-cli delete [id]\n";
        }
        $id = $argv[1];
        $ids = array_filter($this->tasks, fn($task) => $task->id === $id);
        if ($ids) {
            $this->tasks = array_filter($this->tasks, fn($task) => $task->id !== $id);
            $this->saveTasks();
        } else {
            return "Task not found with ID: $id\n";
        }
        return "Task deleted succesfully!\n";
    }

    private function update(array $argv): string
    {
        $count = count($argv);
        if ($count > 3 || $count < 3) {
            return "./task-cli update [id] [newTask]\n";
        }
        $id = $argv[1];
        $ids = array_filter($this->tasks, fn($task) => $task->id === $id);
        if ($ids) {
            $this->tasks[array_keys($ids)[0]]->description = $argv[2];
            $this->tasks[array_keys($ids)[0]]->updatedAt = date("Y-m-d H:i:s");
            $this->saveTasks();
        } else {
            return "Task not found with ID: $id\n";
        }
        return "Task updated succesfully (ID: $id)\n";
    }

    private function create(array $argv): string
    {
        $count = count($argv);
        if ($count > 2 || $count < 2) {
            return "./task-cli create [task]\n";
        }
        $id = count($this->tasks) > 0 ? strval(intval(end($this->tasks)->id) + 1) : '1';
        $this->tasks[] = new Task(
            $id,
            $argv[1],
            TaskStatus::TODO,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        );
        $this->saveTasks();
        return "Task created succesfully (ID: $id)\n";
    }


    private function saveTasks(): void
    {
        file_put_contents(self::FILE, json_encode($this->tasks, JSON_PRETTY_PRINT));
    }

    /**
     * @return Task[]
     */
    private function getOrCreate(): array
    {
        if (!file_exists(self::FILE)) {
            $file = fopen(self::FILE, 'w');
            fwrite($file, json_encode([], JSON_PRETTY_PRINT));
            fclose($file);
        } else {
            if (file_get_contents(self::FILE) === '') {
                file_put_contents(self::FILE, json_encode([], JSON_PRETTY_PRINT));
            }
        }

        $data = json_decode(file_get_contents(self::FILE), true);

        $tasks = array_map(function ($task) {
            return new Task(
                $task['id'],
                $task['description'],
                TaskStatus::from($task['status']),
                $task['createdAt'],
                $task['updatedAt']
            );
        }, $data);

        return $tasks;
    }
}