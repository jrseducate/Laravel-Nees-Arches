/** Dropdown Logic */
function closeAllDropdowns(context)
{
    var elOpenDropdowns = context.querySelectorAll('.na-dropdown.na-open');

    for(var i = 0; i < elOpenDropdowns.length; i++)
        elOpenDropdowns[i].classList.remove('na-open');
}

$('.na-dropdown').on('click', function()
{
    this.classList.toggle('na-open');

    if(!this.classList.contains('na-open'))
        closeAllDropdowns(this);
});
$('.na-body').on('click', function(e)
{
    if (!e.target.matches('.na-dropdown, .na-dropdown-body, .na-dropdown-item'))
        closeAllDropdowns(document);
});

$('.na-dropdown > .na-dropdown-body.na-selectable > .na-dropdown-item').on('click', function()
{
    var elDropdown = this.parentNode;
    var elSelectedItems = elDropdown.querySelectorAll('.na-selected');

    for(var i = 0; i < elSelectedItems.length; i++)
        elSelectedItems[i].classList.remove('na-selected');

    this.classList.add('na-selected');
});

/** Theme Logic */
function showTheme(themeClass)
{
    var themeRegex = new RegExp('\\bna-theme-.*\\b');
    var elTheme = document.querySelector('.na-theme');

    elTheme.className = elTheme.className.replace(themeRegex, '');

    elTheme.classList.add(themeClass);
}
function changeTheme(themeClass)
{
    var elTheme = document.querySelector('.na-theme');

    elTheme.dataset.themeClass = themeClass;

    showTheme(themeClass);
}
function currentTheme()
{
    var elTheme = document.querySelector('.na-theme');

    return elTheme.dataset.themeClass;
}

var $ThemeSelector = $('.na-theme-selector');

$ThemeSelector.on('click', function()
{
    changeTheme(this.dataset.themeClass);
});
$ThemeSelector.on('mouseenter', function()
{
    showTheme(this.dataset.themeClass);
});
$ThemeSelector.on('mouseleave', function()
{
    showTheme(currentTheme());
});

const SwalMixin = {
    Toast: Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    }),
};

Swal.toast = (p) => SwalMixin.Toast.fire(p);

const Toast = {
    fire: function(status, text, custom)
    {
        custom = custom ?? {};

        const common = {
            text: text,
            duration: 5000,
            close: true,
            gravity: 'top',
            position: 'right',
        };

        const statuses = {
            'success': {
                style: {
                    background: 'green',
                    color: 'white',
                }
            },
            'error': {
                style: {
                    background: 'red',
                    color: 'white',
                }
            },
        };

        Toastify($.extend(true, {}, common, statuses[status], custom)).showToast();
    },
};
