<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Fields\Input;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use App\Models\Task;

class TaskScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tasks' => Task::latest()->paginate(4),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Task Management';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Task')
                ->modal('taskModal')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * Create a new task
     */
    public function create(Request $request)
    {
        $request->validate([
            'task.name' => 'required|max:255',
        ]);

        Task::create([
            'name' => $request->input('task.name'),
        ]);

        Toast::info('Task added successfully.');
    }

    /**
     * Edit a task
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'task.name' => 'required|max:255',
        ]);

        $task->update([
            'name' => $request->input('task.name'),
        ]);

        Toast::info('Task updated successfully.');
    }

    /**
     * Delete a task
     */
    public function delete(Task $task)
    {
        $task->delete();

        Toast::info('Task deleted successfully.');
    }

    /**
     * Load task details for editing (async).
     */
    public function asyncGetTask(Task $task): iterable
    {
        return [
            'task' => $task,
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('tasks', [
                TD::make('name', 'Task Name')
                    ->sort()
                    ->filter(Input::make()),

                TD::make('created_at', 'Created At')
                    ->sort()
                    ->render(fn($task) => $task->created_at->format('Y-m-d')),

                TD::make('Actions')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn($task) => 
                        ModalToggle::make('Edit')
                            ->modal('editTaskModal')
                            ->method('update')
                            ->modalTitle('Edit Task')
                            ->asyncParameters(['task' => $task->id])
                            ->icon('pencil')
                            ->class('btn btn-primary')
                        . ' ' .
                        Button::make('Delete')
                            ->method('delete', ['task' => $task->id])
                            ->icon('trash')
                            ->confirm('Are you sure you want to delete this task?')
                            ->class('btn btn-danger')
                    ),
            ]),

            Layout::modal('taskModal', Layout::rows([
                Input::make('task.name')
                    ->title('Task Name')
                    ->placeholder('Enter task name'),
            ]))
                ->title('Create Task')
                ->applyButton('Add Task'),

            Layout::modal('editTaskModal', Layout::rows([
                Input::make('task.name')
                    ->title('Task Name')
                    ->placeholder('Update task name'),
            ]))
                ->title('Edit Task')
                ->applyButton('Save Changes')
                ->async('asyncGetTask'),
        ];
    }
}
