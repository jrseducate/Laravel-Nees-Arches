@props(['category'])

@php
    $categoryModel = new \App\Models\TrelloBoardCategory();

    /** @var \App\Models\TrelloBoardCategory $category */
    $category = $categoryModel->newQuery()->where('name', $category)->first();
    $categoryId = $category->encrypt('id');
@endphp

<div class="trello" data-category="{{ $categoryId }}">
    <div class="trello-header">
        <span class="trello-board-name"></span>
        <span class="trello-button trello-create-board">New Board</span>
        <span class="trello-button trello-create-column">New Column</span>
        <span class="trello-board-list"></span>
    </div>

    <div class="trello-board-columns">

    </div>
</div>
