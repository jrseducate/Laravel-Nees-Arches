<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 *
 * @property Collection trello_columns
 */
class TrelloBoard extends BaseModel
{
    public $table = 'trello_boards';

    public $fillable = [
        'name',
    ];

    /**
     * Relation -- Has Many Trello Columns
     *
     * @return HasMany
     */
    public function trello_columns()
    {
        return $this->hasMany(TrelloColumn::class, 'trello_board_id', 'id');
    }
}
