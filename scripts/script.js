window.onload = function() {
    var dtFrom = document.getElementById("validFromId");
    var dtTo = document.getElementById("validToId");

    if (dtFrom != null) {
        rome(dtFrom, { weekStart: 1 });
    }
    if (dtTo != null) {
        rome(dtTo, { weekStart: 1 });
    }
};