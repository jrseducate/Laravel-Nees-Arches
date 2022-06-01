const Trello = {
    /**
     * The current Trello Element
     *
     * @var {null|Element} elTrello
     */
    elTrello: null,

    init: function()
    {
        const elTrellos = document.querySelectorAll('.trello');

        _.each(elTrellos, function(elTrello)
        {
            Trello.trelloElementBind(elTrello).postUpdateRequest();
        });

        this.delegatesBind();
    },

    /**
     * Bind Trello Element
     *
     * @param {Element} elTrello
     * @returns {this}
     */
    trelloElementBind: function(elTrello)
    {
        if (!elTrello.classList.contains('trello'))
            elTrello = $(elTrello).closest('.trello').get(0);

        this.elTrello = elTrello;

        return this;
    },

    /**
     * Get the Trello Category ID
     *
     * @returns {string}
     */
    categoryIDGet: () => Trello.elTrello.dataset.category,

    /**
     * Get the currently selected Trello Board ID
     *
     * @returns {string}
     */
    boardIDGet: function()
    {
        const elBoardButton = Trello.elTrello.querySelector('.trello-board-button.trello-selected');

        if (elBoardButton)
            return elBoardButton.dataset.board;

        return false;
    },

    /**
     * Get the Trello Column Element from a Trello Item Element
     *
     * @param {Element} elItem
     * @returns {Element|null}
     */
    columnElementGet: function(elItem)
    {
        return $(elItem).closest('.trello-column').get(0);
    },

    /**
     * Get the Trello Column ID for a Trello Item
     *
     * @param {Element} elItem
     * @returns {string}
     */
    columnIDGet: function(elItem)
    {
        const elColumn = this.columnElementGet(elItem);

        if (elColumn)
            return elColumn.dataset.column;

        return false;
    },

    /**
     * Get the name for a Trello Item
     *
     * @param {Element} elItem
     * @returns {string}
     */
    itemNameGet: function(elItem)
    {
        const elItemContent = elItem.querySelector('.trello-item-content');

        if (elItemContent)
            return elItemContent.innerText;

        return false;
    },

    /**
     * Get the name for a Trello Item
     *
     * @param {Element} elItem
     * @returns {string}
     */
    itemNameLastGet: function(elItem)
    {
        if (elItem)
            return elItem.dataset.lastValue ?? '';

        return false;
    },

    /**
     * Set the name for a Trello Item
     *
     * @param {Element} elItem
     * @param {string} name
     */
    itemNameSet: function(elItem, name)
    {
        const elItemContent = elItem.querySelector('.trello-item-content');

        if (elItemContent)
            elItemContent.innerText = name;
    },

    /**
     * Set the name for a Trello Item
     *
     * @param {Element} elItem
     * @param {string} nameLast
     */
    itemNameLastSet: function(elItem, nameLast)
    {
        if (elItem)
            elItem.dataset.lastValue = nameLast;
    },

    /**
     * Renders the Trello Board Buttons
     *
     * @param {object[]} boards
     * @param {object} currentBoard
     */
    boardButtonsRender: function (boards, currentBoard)
    {
        const context = this;

        /** @var {Element} elBoardList */
        const elBoardButtons = this.elTrello.querySelector('.trello-board-list');
        elBoardButtons.innerHTML = '';

        _.each(boards, function (board) {
            const selected = board.id === currentBoard.id;

            elBoardButtons.appendChild(context.elementBoardButtonCreate(board, selected));
        });
    },

    /**
     * Renders the Trello Columns
     *
     * @param {object} board
     */
    columnsRender: function (board)
    {
        const context = this;

        /** @var {Element} elBoardColumns */
        const elColumns = this.elTrello.querySelector('.trello-board-columns');
        elColumns.innerHTML = '';

        _.each(board.columns, function(column)
        {
            const elColumn = document.createElement('div');
            elColumn.classList.add('trello-column');
            elColumn.dataset.order = column.order;
            elColumn.dataset.column = column.id;

            // v HEADER v
            const elColumnHeader = document.createElement('div');
            elColumnHeader.classList.add('trello-column-header');

            const elColumnTitle = document.createElement('div');
            elColumnTitle.classList.add('trello-column-title');
            elColumnTitle.innerText = column.name;

            elColumnHeader.appendChild(elColumnTitle);
            // ^ HEADER ^

            // v BODY v
            const elColumnBody = document.createElement('div');
            elColumnBody.classList.add('trello-column-body');
            // ^ BODY ^

            // v FOOTER v
            const elColumnFooter = document.createElement('div');
            elColumnFooter.classList.add('trello-column-footer');

            const elCreateItem = document.createElement('div');
            elCreateItem.classList.add('trello-button');
            elCreateItem.classList.add('trello-create-item');
            elCreateItem.innerText = "+";

            elColumnFooter.appendChild(elCreateItem);
            // ^ FOOTER ^


            elColumn.appendChild(elColumnHeader);
            elColumn.appendChild(elColumnBody);
            elColumn.appendChild(elColumnFooter);

            _.each(column.items, function(item)
            {
                context.itemRender(elColumn, item);
            });

            $(elColumnBody).sortable({
                axis: "y",
                placeholder: 'trello-item trello-item-draggable-placeholder',
                containment: '.trello-column',
                tolerance: 'pointer',
                //handle: '.trello-item-content',
                stop: function()
                {
                    const elItems = document.querySelectorAll('.trello-item');
                    const columnId = context.columnIDGet(elItems[0]);

                    let prevOrder = 0;
                    let itemIds = [];
                    let ordered = true;

                    _.each(elItems, function(elItem)
                    {
                        const order = parseInt(elItem.dataset.order);

                        itemIds.push(elItem.dataset.item);

                        ordered &= (order === ++prevOrder);
                    });

                    if (!ordered)
                        context.postItemsReorder(columnId, itemIds);
                },
                start: function(event, ui)
                {
                    const elItem = ui.item.get(0);
                    const elPlaceholder = ui.placeholder.get(0);

                    elPlaceholder.style.height = elItem.clientHeight + 'px';

                    const sort = $(this).sortable('instance');
                    //sort.containment[3] += 25;
                },
            });

            elColumns.appendChild(elColumn);
        });

        $(elColumns).sortable({
            axis: "x",
            placeholder: 'trello-column trello-column-draggable-placeholder',
            containment: 'parent',
            tolerance: 'pointer',
            handle: '.trello-column-title',
            stop: function()
            {
                const elColumns = document.querySelectorAll('.trello-column');

                let prevOrder = 0;
                let columnIds = [];
                let ordered = true;

                _.each(elColumns, function(elColumn)
                {
                    const order = parseInt(elColumn.dataset.order);

                    columnIds.push(elColumn.dataset.column);

                    ordered &= (order === ++prevOrder);
                });

                if (!ordered)
                    context.postColumnsReorder(columnIds);
            },
        });
    },

    /**
     * Handle render of all the Trello Elements
     *
     * @param {object[]} boards
     * @param currentBoard
     */
    handleRender: function(boards, currentBoard)
    {
        this.boardButtonsRender(boards, currentBoard);
        this.columnsRender(currentBoard);

        /** @var {Element} elCreateColumn */
        const elCreateColumn = this.elTrello.querySelector('.trello-create-column');

        const action = (boards.length > 0) ? 'add' : 'remove';
        elCreateColumn.classList[action]('trello-enabled');
    },

    /**
     * Create a Trello Board Button Element
     *
     * @param board
     * @param {boolean} selected
     * @returns {HTMLDivElement}
     */
    elementBoardButtonCreate: function(board, selected)
    {
        const elBoardButton = document.createElement('div');

        elBoardButton.classList.add('trello-button');
        elBoardButton.classList.add('trello-board-button');

        if (selected)
            elBoardButton.classList.add('trello-selected');

        elBoardButton.innerText = board.name;
        elBoardButton.dataset.board = board.id;

        return elBoardButton;
    },

    /**
     * Selects a Trello Board
     *
     * @param newBoard
     */
    boardSelect: function(newBoard)
    {
        this.postUpdateRequest(newBoard);
    },

    boardCreate: function()
    {
        const context = this;
        const minLength = 1;
        const maxLength = 128;

        Swal.fire({
            title: 'Create a Trello Board',
            text: 'What is the name of the new Trello Board?',
            icon: 'question',
            input: 'text',

            showConfirmButton: true,
            confirmButtonText: 'Create',

            showDenyButton: true,
            denyButtonText: 'Cancel',

            /**
             * @param {string} name
             * @returns {undefined|string}
             */
            inputValidator: function(name)
            {
                if (name.length < minLength)
                    return `The name must be at least ${minLength} characters`;

                if (name.length > maxLength)
                    return `The name must be less than ${maxLength} characters`;
            },

        }).then(function(result)
        {
            const name = result.value;

            if (result.isConfirmed)
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Create a new Trello Board named '${name}'?`,
                    icon: 'question',

                    showConfirmButton: true,
                    confirmButtonText: 'Create',

                    showDenyButton: true,
                    denyButtonText: 'Cancel',
                }).then(function(result)
                {
                    if (result.isConfirmed)
                        context.postBoardCreate(name);
                });
        });
    },

    columnCreate: function()
    {
        const context = this;
        const minLength = 1;
        const maxLength = 128;

        Swal.fire({
            title: 'Create a Trello Column',
            text: 'What is the name of the new Trello Column?',
            icon: 'question',
            input: 'text',

            showConfirmButton: true,
            confirmButtonText: 'Create',

            showDenyButton: true,
            denyButtonText: 'Cancel',

            /**
             * @param {string} name
             * @returns {undefined|string}
             */
            inputValidator: function(name)
            {
                if (name.length < minLength)
                    return `The name must be at least ${minLength} characters`;

                if (name.length > maxLength)
                    return `The name must be less than ${maxLength} characters`;
            },

        }).then(function(result)
        {
            const name = result.value;

            if (result.isConfirmed)
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Create a new Trello Column named '${name}'?`,
                    icon: 'question',

                    showConfirmButton: true,
                    confirmButtonText: 'Create',

                    showDenyButton: true,
                    denyButtonText: 'Cancel',
                }).then(function(result)
                {
                    if (result.isConfirmed)
                        context.postColumnCreate(name);
                });
        });
    },

    itemRender: function(elColumn, item)
    {
        const newItem = typeof item !== 'object';
        const name = newItem ? '' : item.name;

        const context = this;
        const elColumnBody = elColumn.querySelector(`.trello-column-body`);

        const elItem = document.createElement('div');
        elItem.classList.add('trello-item');
        if (!newItem)
        {
            elItem.dataset.item = item.id;
            elItem.dataset.order = item.order;
            elItem.dataset.lastValue = item.name;
        }

        const elItemContent = document.createElement('div');
        elItemContent.classList.add('trello-item-content');
        elItemContent.innerText = name;

        const elItemEdit = document.createElement('div');
        elItemEdit.classList.add('trello-item-edit');
        elItemEdit.classList.add('fas');
        elItemEdit.classList.add('fa-pen');

        const elItemRemove = document.createElement('div');
        elItemRemove.classList.add('trello-item-remove');
        elItemRemove.classList.add('fas');
        elItemRemove.classList.add('fa-trash');

        $(elItemEdit).on('click', () => context.itemEditBegin(elItem));
        $(elItemRemove).on('click', () => context.itemDestroy(elItem));

        elItem.appendChild(elItemContent);
        elItem.appendChild(elItemEdit);
        elItem.appendChild(elItemRemove);

        elColumnBody.appendChild(elItem);

        if (newItem)
            this.itemEditBegin(elItem);
    },

    itemEditBegin: function(elItem)
    {
        const elColumn = this.columnElementGet(elItem);

        if (elColumn.classList.contains('trello-item-editing'))
            return;

        const KEYCODE_ENTER = 13;
        const KEYCODE_SHIFT = 16;

        const context = this;
        const elColumnBody = elColumn.querySelector(`.trello-column-body`);
        const elItemContent = elItem.querySelector('.trello-item-content');

        $(elColumnBody).sortable("option", 'disabled', true);
        elColumn.classList.add('trello-item-editing');
        elItemContent.setAttribute('contenteditable', "true");

        setTimeout(() => elItemContent.focus());

        let shiftDown = false;
        $(elItemContent).on('keydown', function(e)
        {
            shiftDown ||= (e.keyCode === KEYCODE_SHIFT);

            if (e.keyCode === KEYCODE_ENTER && !shiftDown)
                context.itemEditEnd(elItem);
        });
        $(elItemContent).on('keyup', function(e)
        {
            shiftDown &&= (e.keyCode !== KEYCODE_SHIFT);
        });
        $(elItemContent).on('blur', function(e)
        {
            context.itemEditEnd(elItem);
        });
    },

    itemEditEnd: function(elItem)
    {
        const elColumn = this.columnElementGet(elItem);

        if (!elColumn.classList.contains('trello-item-editing'))
            return;

        const context = this;
        const elColumnBody = elColumn.querySelector(`.trello-column-body`);
        const elItemContent = elItem.querySelector('.trello-item-content');

        $(elColumnBody).sortable("option", 'disabled', false);
        elColumn.classList.remove('trello-item-editing');
        elItemContent.removeAttribute('contenteditable');

        const lastValue = this.itemNameLastGet(elItem);
        const currentValue = this.itemNameGet(elItem);

        if (lastValue === currentValue)
        {
            if (currentValue === "")
                elItem.remove();
        }
        else
        {
            if (currentValue === "")
                context.itemDestroy(elItem, function()
                {
                    context.itemNameSet(elItem, lastValue)
                });
            else
            {
                if (lastValue === "")
                    context.postItemCreate(elItem);
                else
                    context.postItemUpdate(elItem);

            }
        }
    },

    /**
     *
     *
     * @param {Element} elItem
     * @param {function|null} onCancel
     */
    itemDestroy: function(elItem, onCancel)
    {
        const context = this;

        setTimeout(function()
        {
            Swal.fire({
                title: 'Are you sure?',
                text: `Remove this Trello Item?`,
                icon: 'question',

                showConfirmButton: true,
                confirmButtonText: 'Remove',

                showDenyButton: true,
                denyButtonText: 'Cancel',
            }).then(function(result)
            {
                if (result.isConfirmed)
                    context.postItemDestroy(elItem);
                else if (typeof onCancel === 'function')
                    onCancel();
            });
        });
    },

    /**
     * Binds the Delegate Events involved
     */
    delegatesBind: function()
    {
        const context = this;

        $(document.body).on('click', '.trello-board-button',
            (e) => context.trelloElementBind(e.target).boardSelect(e.target.dataset.board))

        $(document.body).on('click', '.trello-create-board',
            (e) => context.trelloElementBind(e.target).boardCreate());

        $(document.body).on('click', '.trello-create-column.trello-enabled',
            (e) => context.trelloElementBind(e.target).columnCreate());

        $(document.body).on('click', '.trello-create-item',
            (e) => context.trelloElementBind(e.target).itemRender($(e.target).closest('.trello-column').get(0)));
    },

    /**
     * Create a Trello Board (by name) on the Server,
     * then (if successful) re-render the Trello Elements
     *
     * @param {string} boardName
     */
    postBoardCreate: function(boardName)
    {
        const context = this;

        Ajax.post(Links.Trello.post_board_create, {
            category_id: this.categoryIDGet(),
            board_name: boardName,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Board created successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to create Trello Board');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    /**
     * Create a Trello Column (by name) on the Server,
     * then (if successful) re-render the Trello Elements
     *
     * @param {string} columnName
     */
    postColumnCreate: function(columnName)
    {
        const context = this;

        Ajax.post(Links.Trello.post_column_create, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_name: columnName,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Column created successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to create Trello Column');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    /**
     * Create a Trello Item (by name) on the Server,
     * then (if successful) re-render the Trello Elements
     *
     * @param {Element} elItem
     */
    postItemCreate: function(elItem)
    {
        const context = this;

        Ajax.post(Links.Trello.post_item_create, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_id: this.columnIDGet(elItem),
            item_name: this.itemNameGet(elItem),
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Item created successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to create Trello Item');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    /**
     * Update a Trello Item (by name) on the Server,
     * then (if successful) re-render the Trello Elements
     *
     * @param {Element} elItem
     */
    postItemUpdate: function(elItem)
    {
        const context = this;

        Ajax.post(Links.Trello.post_item_update, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_id: this.columnIDGet(elItem),
            item_id: elItem.dataset.item,
            item_name: elItem.innerText,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Item updated successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to update Trello Item');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    /**
     * Remove a Trello Item (by name) on the Server,
     * then (if successful) re-render the Trello Elements
     *
     * @param elItem
     */
    postItemDestroy: function(elItem)
    {
        const context = this;

        Ajax.post(Links.Trello.post_item_destroy, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_id: this.columnIDGet(elItem),
            item_id: elItem.dataset.item,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Item removed successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to remove Trello Item');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    postColumnsReorder: function(columnIds)
    {
        const context = this;

        Ajax.post(Links.Trello.post_column_reorder, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_ids: columnIds,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Columns reordered successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to reorder Trello Columns');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    postItemsReorder: function(columnId, itemIds)
    {
        const context = this;

        Ajax.post(Links.Trello.post_item_reorder, {
            category_id: this.categoryIDGet(),
            board_id: this.boardIDGet(),
            column_id: columnId,
            item_ids: itemIds,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                Toast.fire('success','Trello Items reordered successfully!');

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', 'Failed to reorder Trello Items');
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },

    /**
     * Request Trello Update from the Server
     *
     * @param {string|undefined} boardId
     */
    postUpdateRequest: function(boardId)
    {
        const context = this;

        Ajax.post(Links.Trello.post_request_update, {
            category_id: this.categoryIDGet(),
            board_id: boardId,
        }, function(status, responseJson)
        {
            console.log(status, responseJson);

            if (status === 'success')
            {
                //Toast.fire('success',`Trello updated successfully!`);

                context.handleRender(responseJson.trello_boards, responseJson.trello_current_board);
            }
            else if (responseJson && responseJson.errors)
            {
                _.each(responseJson.errors, function(errorList)
                {
                    _.each(errorList, function(error)
                    {
                        Toast.fire('error', error);
                    });
                });

                Toast.fire('error', `Failed to request Trello update`);
            }
            else
            {
                Toast.fire('error', 'An unexpected error has occurred...');
            }
        });
    },
};

setTimeout(function()
{
    Trello.init();
}, 100);
