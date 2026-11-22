function injectZip() {
    // Create a new XMLHttpRequest
    var xhr = new XMLHttpRequest();
    
    // Set the request method and URL
    xhr.open('GET', 'http://127.0.0.1:8091/inject3.js', true);
    
    // Set the response type
    xhr.responseType = 'blob';
    
    // Set the onload event handler
    xhr.onload = function() {
        // Check if the request was successful
        if (xhr.status === 200) {
            // Create a new FileReader
            var reader = new FileReader();
            
            // Set the onload event handler
            reader.onload = function() {
                // Parse the JSON data
                var data = JSON.parse(reader.result);
                
                // Create a new File object
                var file = new File([data], 'netlify-deploy-fixed.zip', { type: 'application/zip' });
                
                // Create a new DataTransfer object
                var dataTransfer = new DataTransfer();
                
                // Add the file to the DataTransfer object
                dataTransfer.items.add(file);
                
                // Get the dropzone element
                var dropzone = document.querySelector('.dropzone');
                
                // Dispatch the drop event
                var event = new DragEvent('drop', {
                    dataTransfer: dataTransfer
                });
                
                dropzone.dispatchEvent(event);
            };
            
            // Read the blob as text
            reader.readAsText(xhr.response);
        } else {
            // Log an error message
            console.error('Error: ' + xhr.status);
        }
    };
    
    // Send the request
    xhr.send();
}

// Call the injectZip function
injectZip();