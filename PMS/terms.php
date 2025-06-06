<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - PharmaCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#e6f7f5',
                            100: '#ccefec',
                            200: '#99dfda',
                            300: '#66cfc7',
                            400: '#33bfb5',
                            500: '#00afa2', // Main primary color
                            600: '#008c82',
                            700: '#006961',
                            800: '#004641',
                            900: '#002320',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 md:px-6 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-capsules text-primary-500 text-2xl mr-2"></i>
                <span class="text-primary-600 font-bold text-xl">PharmaCare</span>
            </div>
            <a href="register.php" class="text-primary-600 hover:text-primary-700 transition">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Registration
            </a>
        </div>
    </nav>

    <!-- Terms Content -->
    <div class="container mx-auto px-4 md:px-6 py-8 flex-grow">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-primary-600 mb-6">Terms and Conditions</h1>
            
            <div class="space-y-6 text-gray-700">
                <section>
                    <h2 class="text-lg font-semibold mb-2">1. Acceptance of Terms</h2>
                    <p>By using PharmaCare Management System, you agree to comply with and be bound by these terms governing the management of pharmaceutical inventory, patient data, and pharmacy operations.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">2. User Responsibilities</h2>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Maintain accurate medication records and inventory data</li>
                        <li>Ensure compliance with pharmaceutical regulations</li>
                        <li>Protect patient confidentiality and prescription data</li>
                        <li>Verify medication interactions and dosage information</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">3. Data Privacy</h2>
                    <p>We adhere to HIPAA and GDPR regulations for handling protected health information (PHI). All prescription data and patient records will be stored securely and accessed only by authorized personnel.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">4. Account Management</h2>
                    <p>Pharmacist accounts must be verified with valid professional credentials. Users are responsible for maintaining the security of their login credentials.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">5. Intellectual Property</h2>
                    <p>All medication databases, inventory management algorithms, and pharmacy workflow systems are proprietary property of PharmaCare.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">6. Limitations of Liability</h2>
                    <p>While we strive for accuracy in drug interaction checks and inventory management, PharmaCare shall not be liable for clinical decisions made based on system recommendations.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">7. Governing Law</h2>
                    <p>These terms shall be governed by pharmaceutical regulations and medical device laws applicable in the Sri Lankan jurisdiction.</p>
                </section>

                <section>
                    <h2 class="text-lg font-semibold mb-2">8. Contact Information</h2>
                    <p>For questions regarding these terms, contact our Pharmacy Compliance Officer at <a href="pharmecy@gmail.com" class="text-primary-600 hover:underline">pharmecy@gmail.com</a></p>
                </section>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-6 shadow-inner mt-8">
        <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
            <p>&copy; 2025 PharmaCare Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>