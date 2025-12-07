<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Generated Manga Storyboard</h2>
    <a href="/" class="btn">Create New</a>
</div>

<?php if (empty($panels)): ?>
    <div class="manga-page" style="text-align: center;">
        <p>No content generated yet or processing failed.</p>
        <p><a href="/">Try again</a></p>
    </div>
<?php else: ?>
    <div class="manga-page">
        <?php foreach ($panels as $panel): ?>
            <div class="panel">
                <div class="panel-number"><?php echo $panel['panel_number']; ?></div>

                <?php if (!empty($panel['image_path'])): ?>
                    <img src="<?php echo $panel['image_path']; ?>" alt="Panel Image">
                <?php else: ?>
                    <div style="background:#eee; height:300px; display:flex; align-items:center; justify-content:center; color:#888;">
                        [Image Generation Failed or Pending]
                    </div>
                <?php endif; ?>

                <?php if (!empty($panel['script_text'])): ?>
                    <div class="dialogue-box">
                        <?php echo nl2br(htmlspecialchars($panel['script_text'])); ?>
                    </div>
                <?php endif; ?>

                <div style="padding: 10px; font-size: 0.8rem; color: #777;">
                    <strong>AI Prompt:</strong> <?php echo htmlspecialchars($panel['image_prompt']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
