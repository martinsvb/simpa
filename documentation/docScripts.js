function toggle(id)
{
    var el = document.getElementById(id);
    var opened = el.classList.contains('icon-plus');
    el.className = opened ? el.className.replace('-plus', '-minus') : el.className.replace('-minus', '-plus');
    var elBody = document.getElementById(id + '-body');
    opened ? elBody.classList.remove('hide') : elBody.classList.add('hide');
}

function docHeaderClicked(id)
{
    document.querySelectorAll('.docHeaderItem').forEach((el) => {
        el.classList.remove('docHeaderItemSelected');
    });
    document.querySelectorAll('.docBodyItem').forEach((el) => {
        el.classList.add('hide');
    });
    document.getElementById(id).classList.add('docHeaderItemSelected');
    document.getElementById(id + 'Body').classList.remove('hide');
}

function docOpenDatabaseTable(table)
{
    document.querySelectorAll('.docHeaderItem').forEach((el) => {
        el.classList.remove('docHeaderItemSelected');
    });
    document.querySelectorAll('.docBodyItem').forEach((el) => {
        el.classList.add('hide');
    });
    document.getElementById('database').classList.add('docHeaderItemSelected');
    document.getElementById('databaseBody').classList.remove('hide');
    document.querySelectorAll('.databaseTable').forEach((el) => {
        el.classList.add('hide');
    });
    document.getElementById(table + '-body').classList.remove('hide');
    var el = document.getElementById(table);
    el.className = el.className.replace('-plus', '-minus');
}
