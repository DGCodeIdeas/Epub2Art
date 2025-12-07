<div class="manga-page">
    <h2>Generate New Manga</h2>
    <p>Upload an EPUB chapter or paste a scene to generate a manga storyboard.</p>

    <form action="/upload" method="post" enctype="multipart/form-data">
        <div>
            <label for="epub">Option 1: Upload EPUB File</label>
            <input type="file" name="epub" id="epub" accept=".epub">
        </div>

        <div style="text-align: center; margin: 10px 0; font-weight: bold;">OR</div>

        <div>
            <label for="text_content">Option 2: Paste Text Snippet</label>
            <textarea name="text_content" id="text_content" rows="12" placeholder="The rain fell hard on the neon-lit streets of Neo-Tokyo. Kaito adjusted his coat collar, his cybernetic eye scanning the crowd for the rogue android..."></textarea>
        </div>

        <button type="submit" class="btn">Create Manga Storyboard</button>
    </form>
</div>
