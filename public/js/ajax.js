const Ajax = {
    init: function()
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': Ajax.csrf(),
            }
        });
    },
    csrf: () => document.head.querySelector("meta[name=\"csrf-token\"]").content,
    request: function(method, url, params, callback)
    {
        $.ajax(url, {
            header: {
                'X-CSRF-TOKEN': this.csrf(),
            },
            async: true,
            method: method,
            complete: (response, status) => callback(status, response.responseJSON, response),
            data: params,
            dataType: 'json',
        });
    },
    get: (url, params, callback) => Ajax.request('get', url, params, callback),
    post: (url, params, callback) => Ajax.request('post', url, params, callback),
};

Ajax.init();
