<?php include 'includes/header.php'; ?>
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Health Blog</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Blog posts would be dynamically loaded from database -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://via.placeholder.com/600x400?text=Blog+Post" alt="Blog Post" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">June 15, 2023</div>
                    <h3 class="text-xl font-bold mb-2">10 Tips for a Healthy Heart</h3>
                    <p class="text-gray-600 mb-4">Learn simple lifestyle changes that can significantly improve your heart health and reduce the risk of cardiovascular diseases.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Read More</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://via.placeholder.com/600x400?text=Blog+Post" alt="Blog Post" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">May 28, 2023</div>
                    <h3 class="text-xl font-bold mb-2">Understanding Seasonal Allergies</h3>
                    <p class="text-gray-600 mb-4">Everything you need to know about seasonal allergies, symptoms, and effective treatments to manage them.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Read More</a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <img src="https://via.placeholder.com/600x400?text=Blog+Post" alt="Blog Post" class="w-full h-48 object-cover">
                <div class="p-6">
                    <div class="text-sm text-gray-500 mb-2">April 10, 2023</div>
                    <h3 class="text-xl font-bold mb-2">The Importance of Regular Check-ups</h3>
                    <p class="text-gray-600 mb-4">Why preventive healthcare through regular medical check-ups is crucial for early detection of health issues.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Read More</a>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>