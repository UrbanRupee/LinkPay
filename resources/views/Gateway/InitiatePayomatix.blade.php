<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payomatix</title>
    <style>
        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <!-- Iframe to load the Payomatix URL -->
    <iframe id="payomatixFrame" src="{{$url}}" width="100%" height="600px"></iframe>

    <script>
        // Wait for the iframe to load
        const iframe = document.getElementById('payomatixFrame');

        iframe.onload = function() {
            try {
                // Access the iframe's document content
                const iframeDocument = iframe.contentWindow.document;
                
                // Find the image by its alt attribute and replace its source
                const logoImage = iframeDocument.querySelector('img[alt="Payomatix"]');
                
                if (logoImage) {
                    logoImage.src = "https://pushpendratechnology.com/storage/theme/assets/dist/images/logo.png";
                    console.log('Logo updated successfully');
                } else {
                    console.log('Logo image not found');
                }
            } catch (error) {
                console.error('Error accessing iframe content:', error);
                alert("Unable to modify content due to cross-origin restrictions.");
            }
        };
    </script>

</body>
</html>
