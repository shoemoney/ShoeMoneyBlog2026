<footer class="bg-gray-100 border-t border-gray-200 mt-auto">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
            {{-- Copyright --}}
            <p class="text-sm text-gray-600">
                &copy; {{ date('Y') }} ShoeMoney. All rights reserved.
            </p>

            {{-- Secondary Links --}}
            <nav class="flex items-center space-x-6 text-sm text-gray-600">
                <a href="/privacy/" class="hover:text-gray-900 transition-colors">
                    Privacy
                </a>
                <a href="/terms/" class="hover:text-gray-900 transition-colors">
                    Terms
                </a>
            </nav>
        </div>
    </div>
</footer>
