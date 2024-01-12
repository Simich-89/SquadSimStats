<!DOCTYPE html>
<html lang="uk">
<head><title>4.5.0. server</title><meta charset="utf-8">
<script src="js/jq.js" ></script>
<style>
[draggable="true"] {
  user-select: none;
  -moz-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
}

ul.moveable {
  list-style: none;
  margin: 0px;

  li {
    list-style-image: none;
    margin: 10px;
    border: 1px solid #ccc;
    padding: 4px;
    border-radius: 4px;
    color: #666;
    cursor: move;

    &:hover {
      background-color: #eee;
    }
  }
}
</style>
</head>
<body>
<ul id="items-list" class="moveable">
    <li>One</li>
    <li>Two</li>
    <li>Three</li>
    <li>Four</li>
</ul>

<script>
let items = document.querySelectorAll('#items-list > li')

items.forEach(item => {
  $(item).prop('draggable', true)
  item.addEventListener('dragstart', dragStart)
  item.addEventListener('drop', dropped)
  item.addEventListener('dragenter', cancelDefault)
  item.addEventListener('dragover', cancelDefault)
})

function dragStart (e) {
  var index = $(e.target).index()
  e.dataTransfer.setData('text/plain', index)
}

function dropped (e) {
  cancelDefault(e)
  
  // get new and old index
  let oldIndex = e.dataTransfer.getData('text/plain')
  let target = $(e.target)
  let newIndex = target.index()
  
  // remove dropped items at old place
  let dropped = $(this).parent().children().eq(oldIndex).remove()

  // insert the dropped items at new place
  if (newIndex < oldIndex) {
    target.before(dropped)
  } else { 
    target.after(dropped)
  }
}

function cancelDefault (e) {
  e.preventDefault()
  e.stopPropagation()
  return false
}

</script>

</body>
</html>