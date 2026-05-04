<?php
$currentView = 'submit-request';
require_once '../includes/header.php';
?>

<div class="section-header">
  <h2><i class="fas fa-plus-circle me-2"></i><?php echo t('submitRequest'); ?></h2>
</div>

<div class="glass-card" style="max-width: 700px; margin: 0 auto;">
  <form id="submitRequestForm" onsubmit="event.preventDefault(); RecLise.submitNewRequest();">
    <div class="mb-3">
      <label class="form-label"><?php echo t('type'); ?></label>
      <select class="form-select" id="reqType">
        <option value="request"><?php echo t('typeRequest'); ?></option>
        <option value="complaint"><?php echo t('typeComplaint'); ?></option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('title'); ?></label>
      <input type="text" class="form-control" id="reqTitle" placeholder="<?php echo t('title'); ?>">
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('category'); ?></label>
      <select class="form-select" id="reqCategory">
        <option value="technical"><?php echo t('technical'); ?></option>
        <option value="access"><?php echo t('access'); ?></option>
        <option value="training"><?php echo t('trainingCat'); ?></option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('priority'); ?></label>
      <select class="form-select" id="reqPriority">
        <option value="low"><?php echo t('low'); ?></option>
        <option value="medium"><?php echo t('medium'); ?></option>
        <option value="high"><?php echo t('high'); ?></option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('description'); ?></label>
      <textarea class="form-control" id="reqDesc" rows="5" placeholder="<?php echo t('description'); ?>"></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('attachments'); ?></label>
      <input type="file" class="form-control" id="reqFiles" multiple>
      <small class="text-secondary"><?php echo t('noResults'); ?> (Optional)</small>
    </div>
    <button type="submit" class="btn btn-neon w-100">
      <i class="fas fa-paper-plane me-2"></i><?php echo t('submit'); ?>
    </button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
