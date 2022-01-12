<head>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
    }
    
    .linkslist-main {
      min-height: 100vh;
      background: red;
    }
  </style>
</head>

<div class="linkslist-main">
  <?php echo LinkList::Foo();?>
  <?php echo LinkList::Bar();?>
</div>
