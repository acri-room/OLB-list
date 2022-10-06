function olblistPrevDate() {
  var list = document.getElementById("olblist_date");
  if (list.selectedIndex != 0)
    list.selectedIndex -= 1;
}
function olblistNextDate() {
  var list = document.getElementById("olblist_date");
  if (list.selectedIndex != list.length - 1)
    list.selectedIndex += 1;
}
function olblistTransit() {
  var date = document.getElementById("olblist_date").value;
  var servers = document.getElementById("olblist_server").value;
  var searchStr = "date=" + date.substr(0, 10);
  if (servers != "*")
    searchStr += "&servers=" + servers;
  location.search = searchStr;
}
function olblistDispColumns(target) {
  var columns = document.getElementById("olblist_cols").getElementsByTagName("col");
  for (let column of columns) {
    var visible = false;
    if (target == "*") visible = true;
    else if (column.id == "olblist_col_head") visible = true;
    else if (column.id.substring(column.id.length - 5, column.id.length - 2) == target) visible = true;
    column.style.setProperty("visibility", (visible) ? "visible" : "collapse");
  }
}
window.addEventListener('load', (e) => {
  olblistDispColumns(document.getElementById("olblist_server").value);
});