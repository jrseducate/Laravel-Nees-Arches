<?php

namespace App\Http\Controllers;

use App\Models\TrelloBoard;
use App\Models\TrelloBoardCategory;
use App\Models\TrelloColumn;
use App\Models\TrelloItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TrelloController extends BaseController
{
    /**
     * Get Board Category
     *
     * @param int $categoryId
     * @return TrelloBoardCategory|null
     */
    public function getCategory(int $categoryId) : TrelloBoardCategory|null
    {
        $modelTrelloBoardCategory = new TrelloBoardCategory();

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $modelTrelloBoardCategory->newQuery()->find($categoryId);
    }

    /**
     * Get Board
     *
     * @param TrelloBoardCategory $trelloBoardCategory
     * @param int $boardId
     * @return TrelloBoard|null
     */
    public function getBoard(TrelloBoardCategory $trelloBoardCategory, int $boardId) : TrelloBoard|null
    {
        return $trelloBoardCategory->trello_boards()->find($boardId);
    }

    /**
     * Get Board Exists
     *
     * @param TrelloBoardCategory $trelloBoardCategory
     * @param string $boardName
     * @return bool
     */
    public function getBoardExists(TrelloBoardCategory $trelloBoardCategory, string $boardName) : bool
    {
        return $trelloBoardCategory->trello_boards()->where('name', $boardName)->exists();
    }

    /**
     * Get Column
     *
     * @param TrelloBoard $trelloBoard
     * @param int $columnId
     * @return TrelloColumn|null
     */
    public function getColumn(TrelloBoard $trelloBoard, int $columnId) : TrelloColumn|null
    {
        return $trelloBoard->trello_columns()->find($columnId);
    }

    /**
     * Get Item
     *
     * @param TrelloColumn $trelloColumn
     * @param int $itemId
     * @return TrelloItem|null
     */
    public function getItem(TrelloColumn $trelloColumn, int $itemId) : TrelloItem|null
    {
        return $trelloColumn->trello_items()->find($itemId);
    }

    /**
     * Get Column Exists
     *
     * @param TrelloBoard $trelloBoard
     * @param string $columnName
     * @return bool
     */
    public function getColumnExists(TrelloBoard $trelloBoard, string $columnName) : bool
    {
        return $trelloBoard->trello_columns()->where('name', $columnName)->exists();
    }

    /**
     * The Successful Response of CRUD
     *
     * @param TrelloBoardCategory $trelloBoardCategory
     * @param TrelloBoard|null $trelloBoard
     * @param Collection|null $trelloBoards
     * @return array
     */
    public function response(TrelloBoardCategory $trelloBoardCategory, TrelloBoard|null $trelloBoard = null, Collection|null $trelloBoards = null) : array
    {
        if (!isset($trelloBoards))
            $trelloBoards = $trelloBoardCategory->trello_boards;

        $trelloCurrentBoardData = null;
        if (isset($trelloBoard))
            $trelloCurrentBoardData = [
                'id' => $trelloBoard->encrypt('id'),
                'name' => $trelloBoard->name,
                'columns' => $trelloBoard->trello_columns()->orderBy('order')->get([
                    'id',
                    'name',
                    'order',
                ])->map(function(TrelloColumn $trelloColumn)
                {
                    return [
                        'id' => $trelloColumn->encrypt('id'),
                        'name' => $trelloColumn->name,
                        'order' => $trelloColumn->order,
                        'items' => $trelloColumn->trello_items()->orderBy('order')->get([
                            'id',
                            'name',
                            'order',
                        ])->map(function(TrelloItem $trelloItem)
                        {
                            return [
                                'id' => $trelloItem->encrypt('id'),
                                'name' => $trelloItem->name,
                                'order' => $trelloItem->order,
                            ];
                        }),
                    ];
                }),
            ];

        return [
            'trello_current_board' => $trelloCurrentBoardData,
            'trello_boards' => $trelloBoards->map(function(TrelloBoard $trelloBoard)
            {
                return [
                    'id' => $trelloBoard->encrypt('id'),
                    'name' => $trelloBoard->name,
                ];
            }),
        ];
    }

    /**
     * Error Response
     *
     * @param string $case
     * @param array $data
     * @return JsonResponse
     */
    public function errorResponse(string $case, array $data = []) : JsonResponse
    {
        switch($case)
        {
            case 'category-not-found':
                $errorText = "The category '{$data['category_id']}' does not exist!";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'category-not-found' => [$errorText]
                    ]
                ], 422);
            case 'board-not-found':
                $errorText = "The board '{$data['board_id']}' does not exist!";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'board-not-found' => [$errorText]
                    ]
                ], 422);
            case 'column-not-found':
                $errorText = "The column '{$data['column_id']}' does not exist!";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'column-not-found' => [$errorText]
                    ]
                ], 422);
            case 'item-not-found':
                $errorText = "The item '{$data['item_id']}' does not exist!";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'item-not-found' => [$errorText]
                    ]
                ], 422);

            case 'board-exists':
                $errorText = "The board '{$data['board']}' already exists for the category '{$data['category']}'";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'board-exists' => [$errorText]
                    ]
                ], 422);
            case 'column-exists':
                $errorText = "The column '{$data['column']}' already exists for the board '{$data['board']}' in category '{$data['category']}'";
                return response()->json([
                    'message' => $errorText,
                    'errors' => [
                        'column-exists' => [$errorText]
                    ]
                ], 422);
        }

        $errorText = 'An unknown error has occurred';
        return response()->json([
            'message' => $errorText,
            'errors' => [
                'unknown' => [$errorText]
            ]
        ], 422);
    }

    public function post_board_create(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        if ($this->getBoardExists($trelloBoardCategory, $data['board_name']))
            return $this->errorResponse('board-exists', $data);

        /** @var TrelloBoard $trelloBoard */
        $trelloBoard = $trelloBoardCategory->trello_boards()->create([
            'name' => $data['board_name'],
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_board_update(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'board_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        if ($this->getBoardExists($trelloBoardCategory, $data['board_name']))
            return $this->errorResponse('board-exists', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloBoard->update([
            'name' => $data['board_name'],
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_board_destroy(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloBoard->delete();

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_column_create(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        if ($this->getColumnExists($trelloBoard, $data['column_name']))
            return $this->errorResponse('column-exists', $data);

        $nextOrderIndex = ($trelloBoard->trello_columns()->max('order') ?? 0) + 1;
        $trelloBoard->trello_columns()->create([
            'name' => $data['column_name'],
            'order' => $nextOrderIndex,
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_column_update(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
            'column_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        if ($this->getColumnExists($trelloBoard, $data['column_name']))
            return $this->errorResponse('column-exists', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $trelloColumn->update([
            'name' => $data['column_name'],
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_column_destroy(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $trelloColumn->delete();

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_item_create(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
            'item_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $nextOrderIndex = ($trelloColumn->trello_items()->max('order') ?? 0) + 1;
        $trelloColumn->trello_items()->create([
            'name' => $data['item_name'],
            'order' => $nextOrderIndex,
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_item_update(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
            'item_id' => 'required',
            'item_name' => 'required|max:128',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id', 'item_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $trelloItem = $this->getItem($trelloColumn, $data['item_id']);
        if (empty($trelloItem))
            return $this->errorResponse('item-not-found', $data);

        $trelloItem->update([
            'name' => $data['item_name']
        ]);

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_item_destroy(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
            'item_id' => 'required',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id', 'item_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $trelloItem = $this->getItem($trelloColumn, $data['item_id']);
        if (empty($trelloItem))
            return $this->errorResponse('item-not-found', $data);

        $trelloItem->delete();

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_column_reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_ids' => 'required',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_ids']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $columnOrderMap = collect($data['column_ids'])->mapWithKeys(function($columnId, $index)
        {
            return [$columnId => $index + 1];
        });
        $changed = false;

        $trelloBoard->trello_columns->each(function(TrelloColumn $trelloColumn) use(&$columnOrderMap, &$changed)
        {
            $order = $columnOrderMap->get($trelloColumn->id);
            $changed |= $trelloColumn->order != $order;

            if ($changed)
                $trelloColumn->update(['order' => $order]);
        });

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_item_reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'required',
            'column_id' => 'required',
            'item_ids' => 'required',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id', 'column_id', 'item_ids']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        $trelloBoard = $this->getBoard($trelloBoardCategory, $data['board_id']);
        if (empty($trelloBoard))
            return $this->errorResponse('board-not-found', $data);

        $trelloColumn = $this->getColumn($trelloBoard, $data['column_id']);
        if (empty($trelloColumn))
            return $this->errorResponse('column-not-found', $data);

        $itemOrderMap = collect($data['item_ids'])->mapWithKeys(function($itemId, $index)
        {
            return [$itemId => $index + 1];
        });
        $changed = false;

        $trelloColumn->trello_items->each(function(TrelloItem $trelloItem) use(&$itemOrderMap, &$changed)
        {
            $order = $itemOrderMap->get($trelloItem->id);
            $changed |= $trelloItem->order != $order;

            if ($changed)
                $trelloItem->update(['order' => $order]);
        });

        return $this->response($trelloBoardCategory, $trelloBoard);
    }

    public function post_request_update(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'board_id' => 'nullable',
        ]);
        $data = $this->decryptKeys($data, ['category_id', 'board_id']);

        $trelloBoardCategory = $this->getCategory($data['category_id']);
        if (empty($trelloBoardCategory))
            return $this->errorResponse('category-not-found', $data);

        /** @var TrelloBoard $trelloBoard */
        $trelloBoard = !empty($data['board_id']) ?
            $this->getBoard($trelloBoardCategory, $data['board_id']) :
            $trelloBoardCategory->trello_boards()->first();

        return $this->response($trelloBoardCategory, $trelloBoard);
    }
}
