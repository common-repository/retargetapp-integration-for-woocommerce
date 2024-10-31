<?php include(dirname(__FILE__) . '/head.php'); ?>
<?php include(dirname(__FILE__) . '/card-header.php'); ?>

<?php if ($permalink_structure):
    include(dirname(__FILE__) . '/successfully_installed.php');
else:
    include(dirname(__FILE__) . '/invalid_permalink_type_installed.php');
endif; ?>

<?php include(dirname(__FILE__) . '/card-footer.php'); ?>