<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property int order
 *
 * @property Collection trello_items
 */
class TrelloColumn extends BaseModel
{
    public $table = 'trello_columns';

    public $fillable = [
        'name',
        'order',
    ];

    /**
     * Relation -- Has Many Trello Items
     *
     * @return HasMany
     */
    public function trello_items()
    {
        return $this->hasMany(TrelloItem::class, 'trello_column_id', 'id');
    }
}
