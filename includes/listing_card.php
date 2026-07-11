<?php
/** @var array $p */
$ref = 'REF. ' . str_pad($p['id'], 4, '0', STR_PAD_LEFT);
?>
<a class="listing-card" href="<?= base_url('property.php?id=' . (int)$p['id']) ?>">
  <div class="listing-photo">
    <?php if (!empty($p['image'])): ?>
      <img src="<?= base_url('uploads/property_images/' . h($p['image'])) ?>" alt="<?= h($p['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
    <?php else: ?>
      No photo yet
    <?php endif; ?>
    <span class="ref-tag"><?= h($ref) ?></span>
    <span class="status-pill status-<?= h($p['status']) ?>"><?= h($p['status']) ?></span>
  </div>
  <div class="listing-body">
    <div class="listing-price"><?= money($p['price']) ?></div>
    <div class="listing-title"><?= h($p['title']) ?></div>
    <div class="listing-loc"><?= h($p['location']) ?><?= $p['location'] && $p['city'] ? ', ' : '' ?><?= h($p['city']) ?></div>
    <div class="listing-meta">
      <span><?= h($p['property_type'] ?: '—') ?></span>
      <span><?= (int)($p['bedrooms'] ?? 0) ?> bd</span>
      <span><?= (int)($p['bathrooms'] ?? 0) ?> ba</span>
    </div>
  </div>
</a>
