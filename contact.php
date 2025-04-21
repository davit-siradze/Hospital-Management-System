<?php include 'includes/header.php'; ?>
    <div class="container mx-auto py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold mb-8">Contact Us</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Our Information</h2>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-medium">Address</h3>
                                <p class="text-gray-600">123 Medical Street, Health City, HC 12345</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-phone text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-medium">Phone</h3>
                                <p class="text-gray-600">+1 (123) 456-7890</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-envelope text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-medium">Email</h3>
                                <p class="text-gray-600">info@hospitalms.com</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-clock text-blue-600 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-medium">Working Hours</h3>
                                <p class="text-gray-600">Monday - Friday: 8:00 AM - 6:00 PM</p>
                                <p class="text-gray-600">Saturday: 9:00 AM - 3:00 PM</p>
                                <p class="text-gray-600">Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-4">Send Us a Message</h2>
                    <form class="space-y-4">
                        <div>
                            <label for="name" class="block text-gray-700 mb-2">Your Name</label>
                            <input type="text" id="name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 mb-2">Your Email</label>
                            <input type="email" id="email" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="subject" class="block text-gray-700 mb-2">Subject</label>
                            <input type="text" id="subject" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="message" class="block text-gray-700 mb-2">Message</label>
                            <textarea id="message" rows="4" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">Send Message</button>
                    </form>
                </div>
            </div>
            
            <div>
                <h2 class="text-xl font-semibold mb-4">Our Location</h2>
                <div class="bg-gray-200 h-64 rounded-lg overflow-hidden">
                    <!-- Map placeholder - would be replaced with actual map embed -->
                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                        <i class="fas fa-map-marked-alt text-4xl"></i>
                        <span class="ml-2">Map would be displayed here</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>