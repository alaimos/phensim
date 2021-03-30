    <!-- Page JS Code -->
    <script src="{{ url('js/core.js') }}"></script>
    <script src="{{ url('js/app.js') }}"></script>
    <script>
        jQuery(function () {
            App.initHelpers(['appear', 'appear-countTo', 'select2']);
        });
    </script>
    @stack('inline-scripts')
</body>
</html>