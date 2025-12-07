<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Epub2Art - Novel to Manga Converter</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background: #eef2f5; color: #333; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 1rem 0; text-align: center; margin-bottom: 2rem; }
        .header h1 { margin: 0; }
        .container { max-width: 900px; margin: 0 auto; padding: 0 20px; }

        /* Manga Page Layout */
        .manga-page { background: white; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 40px; }

        .panel {
            border: 3px solid #000;
            margin-bottom: 20px;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .panel img {
            width: 100%;
            height: auto;
            display: block;
            filter: grayscale(100%); /* Make it look more like manga */
            transition: filter 0.3s;
        }
        .panel:hover img { filter: grayscale(0%); }

        .panel-number {
            position: absolute;
            top: 0;
            left: 0;
            background: black;
            color: white;
            padding: 2px 8px;
            font-size: 12px;
            z-index: 10;
        }

        .dialogue-box {
            background: #fff;
            border: 2px solid #000;
            border-radius: 15px;
            padding: 10px 15px;
            margin: 10px;
            font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif;
            font-size: 14px;
            line-height: 1.3;
            box-shadow: 3px 3px 0 rgba(0,0,0,0.2);
        }

        .description-text {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }

        .btn { display: inline-block; padding: 10px 25px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }

        form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type="file"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-bottom: 20px; }
        textarea { font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Epub2Art</h1>
        <p>Convert Novels to Visual Stories</p>
    </div>
    <div class="container">
        <?php echo $content; ?>
    </div>
</body>
</html>
