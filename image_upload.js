// Function to handle form submission and database storage
function saveToDatabase() {
    const imageUpload = document.getElementById('imageUpload');
    const imageDescription = document.getElementById('imageDescription');
    const priority = document.getElementById('priority');
    const assignedTo = document.getElementById('assignedTo');

    // Validate if an image is selected
    if (!imageUpload.files || !imageUpload.files[0]) {
        alert('Please select an image first');
        return;
    }

    // Create FormData object
    const formData = new FormData();
    formData.append('image', imageUpload.files[0]);
    formData.append('description', imageDescription.value);
    formData.append('priority', priority.value);
    formData.append('assignedTo', assignedTo.value);
    formData.append('annotations', JSON.stringify(annotations));

    // Show loading state
    saveButton.disabled = true;
    saveButton.textContent = 'Saving...';

    // Send data to server
    fetch('save_annotation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === "success") {
            alert(data.message);
            // Optionally clear the form or update UI
            updateAnnotationsList();
        } else {
            throw new Error(data.message || 'Failed to save annotations');
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Failed to save annotations: " + error.message);
    })
    .finally(() => {
        // Reset button state
        saveButton.disabled = false;
        saveButton.textContent = 'Save Annotations';
    });
}

// Function to fetch annotations for a specific image
function fetchAnnotations(imageId) {
    fetch(`fetch_annotations.php?image_id=${imageId}`)
        .then(response => response.json())
        .then(data => {
            annotations = data;
            updateAnnotationsList();
            displayAnnotationsOnImage();
        })
        .catch(error => {
            console.error("Error fetching annotations:", error);
            alert("Failed to fetch annotations");
        });
}

// Function to display annotations on the image
function displayAnnotationsOnImage() {
    // Clear existing markers
    const existingMarkers = imageContainer.querySelectorAll('.annotation-marker');
    existingMarkers.forEach(marker => marker.remove());

    // Display each annotation
    annotations.forEach(annotation => {
        createAnnotation(annotation.x, annotation.y, annotation.description);
    });
}

// Update the saveButton event listener
saveButton.addEventListener('click', saveToDatabase);