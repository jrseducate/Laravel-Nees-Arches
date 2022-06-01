<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 *
 * @property Collection trello_boards
 */
class TrelloBoardCategory extends BaseModel
{
    public $table = 'trello_board_categories';

    public $fillable = [
        'name',
    ];

    /**
     * Relation -- Has Many Trello Boards
     *
     * @return HasMany
     */
    public function trello_boards()
    {
        return $this->hasMany(TrelloBoard::class, 'trello_category_id', 'id');
    }
}
