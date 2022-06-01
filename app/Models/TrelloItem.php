<?php

namespace App\Models;

/**
 * @property string name
 * @property int order
 */
class TrelloItem extends BaseModel
{
    public $table = 'trello_items';

    public $fillable = [
        'name',
        'order',
    ];
}
