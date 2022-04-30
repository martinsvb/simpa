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

function clearView()
{
    document.querySelectorAll('.docHeaderItem').forEach((el) => {
        el.classList.remove('docHeaderItemSelected');
    });
    document.querySelectorAll('.docBodyItem').forEach((el) => {
        el.classList.add('hide');
    });
}

function docOpenDatabaseTable(table)
{
    clearView();

    document.getElementById('database').classList.add('docHeaderItemSelected');
    document.getElementById('databaseBody').classList.remove('hide');

    document.querySelectorAll('.databaseTable').forEach((el) => {
        el.classList.add('hide');
    });
    var tableEl = document.getElementById(table + '-body');
    tableEl.classList.remove('hide');
    var tableHeader = document.getElementById(table);
    tableHeader.className = tableHeader.className.replace('-plus', '-minus');
}

function docOpenController(module, controller)
{
    clearView();

    document.getElementById('documentation').classList.add('docHeaderItemSelected');
    document.getElementById('documentationBody').classList.remove('hide');

    document.querySelectorAll('.module').forEach((el) => {
        el.classList.add('hide');
    });
    var moduleEl = document.getElementById(module + '-body');
    moduleEl.classList.remove('hide');
    var moduleHeader = document.getElementById(module);
    moduleHeader.className = moduleHeader.className.replace('-plus', '-minus');

    document.querySelectorAll('.classContent').forEach((el) => {
        el.classList.add('hide');
    });
    const controllerId = `${module}-${controller}`;
    var controllerEl = document.getElementById(`${controllerId}-body`);
    controllerEl.classList.remove('hide');
    var controllerHeader = document.getElementById(controllerId);
    controllerHeader.className = controllerHeader.className.replace('-plus', '-minus');
}
