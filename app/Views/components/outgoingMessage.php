<!-- Outgoing message -->
<div class="d-flex mb-3">
  <div class="ms-5"></div>
  <div class="ms-auto">
    <div class="bg-primary text-white rounded-3 p-3 shadow-sm">
      <p class="mb-0"><?=$message->content?></p>
    </div>
    <small class="text-muted text-end d-block"><?=$message->timestamp?></small>
  </div>
</div>