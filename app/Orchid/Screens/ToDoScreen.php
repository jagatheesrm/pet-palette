<?php

namespace App\Orchid\Screens;

use App\Models\ToDo;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Str;

class ToDoScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'toDos' => ToDo::latest()->get(),
        ];
    }

    public function name(): string
    {
        return 'ToDo Management';
    }

    public function commandBar(): array
    {
        return [
            Button::make('Add ToDo')
                ->icon('plus')
                ->method('createToDo')
                ->modal('createToDoModal'),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::table('toDos', [
                TD::make('title', 'Title')->sort(),

                TD::make('description', 'Description')
                    ->render(fn($toDo) => Str::limit($toDo->description, 50)),

                TD::make('completed', 'Status')
                    ->render(fn($toDo) => $toDo->completed ? '✅ Completed' : '❌ Pending'),

                TD::make('actions', 'Actions')
                    ->render(fn($toDo) => Button::make('Delete')
                        ->icon('trash')
                        ->confirm('Are you sure?')
                        ->method('deleteToDo', ['id' => $toDo->id])),
            ]),

            Layout::modal('createToDoModal', [
                Layout::rows([
                    Input::make('title') // ✅ Changed from 'todo.title' to 'title'
                        ->title('Title')
                        ->placeholder('Enter ToDo title')
                        ->required(),

                    TextArea::make('description') // ✅ Changed from 'todo.description' to 'description'
                        ->title('Description')
                        ->placeholder('Enter ToDo details'),
                ]),
            ])->title('Create ToDo')->applyButton('Save'),
        ];
    }

    public function createToDo(Request $request)
    {
        // 🛑 Debugging: Check what data is being received
        dd($request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255', // ✅ Changed from 'todo.title' to 'title'
            'description' => 'nullable|string',
        ]);

        ToDo::create([
            'title' => $validated['title'], // ✅ Access directly
            'description' => $validated['description'],
            'completed' => false,
        ]);

        Toast::info('ToDo Created Successfully!');

        return redirect()->route('platform.todos');
    }

    public function deleteToDo(Request $request)
    {
        ToDo::findOrFail($request->get('id'))->delete();

        Toast::info('ToDo Deleted Successfully!');

        return redirect()->route('platform.todos');
    }
}
