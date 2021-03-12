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
  location.search = "date=" + document.getElementById("olblist_date").value;
}