<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ProjectBoard;
use Illuminate\Support\Str;

class ProjectBoardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static string $view = 'filament.pages.project-board-page';
    protected static ?string $navigationLabel = 'Pizarra del Proyecto';
    protected static ?string $title = 'Pizarra del Proyecto';
    protected static ?int $navigationSort = 100;

    public array $notes = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'manager']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'manager']);
    }

    public function mount(): void
    {
        $this->loadNotes();
    }

    public function loadNotes()
    {
        $board = ProjectBoard::firstOrCreate(['id' => 1]);
        $content = $board->content;
        
        if (empty($content)) {
            $this->notes = [];
        } else {
            $this->notes = json_decode($content, true) ?? [];
        }
    }

    public function addNote()
    {
        // Colores en formato HEX (amarillo, verde, azul, rosa, morado)
        $colors = ['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#e9d5ff'];
        $color = $colors[array_rand($colors)];

        $this->notes[] = [
            'id' => Str::uuid()->toString(),
            'x' => rand(50, 400),
            'y' => rand(50, 300),
            'content' => '',
            'color' => $color,
            'author' => auth()->user()->name ?? 'Admin',
        ];

        $this->saveNotes();
    }

    public function updateNotePosition($id, $x, $y)
    {
        foreach ($this->notes as &$note) {
            if ($note['id'] === $id) {
                $note['x'] = $x;
                $note['y'] = $y;
                break;
            }
        }
        $this->saveNotes();
    }

    public function updateNoteContent($id, $content)
    {
        foreach ($this->notes as &$note) {
            if ($note['id'] === $id) {
                $note['content'] = $content;
                break;
            }
        }
        $this->saveNotes();
    }

    public function deleteNote($id)
    {
        $this->notes = array_filter($this->notes, fn($note) => $note['id'] !== $id);
        $this->notes = array_values($this->notes); // Reindex array
        $this->saveNotes();
    }

    private function saveNotes()
    {
        $board = ProjectBoard::firstOrCreate(['id' => 1]);
        $board->update(['content' => json_encode($this->notes)]);
    }
}
