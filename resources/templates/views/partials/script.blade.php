<script>
    var DMS = {
        version: '{{ isset($currentVersion) ? $currentVersion : \DcatPage\default_version() }}',

        getDocUrl: function (doc) {
            var temp = '{{ \DcatPage\doc_url('{doc}', isset($currentVersion) ? $currentVersion : \DcatPage\default_version()) }}';

            if (location.pathname.indexOf(temp.replace('{doc}.html', '')) !== -1) {
                return doc+'.html';
            }

            return temp.replace('{doc}', doc);
        },

        config: {
            comment: {!! json_encode(\DcatPage\config('comment') ?: []) !!}
        },
    };
    (function () {
        function indices() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = '{{ \DcatPage\asset('assets/indices/'. (isset($currentVersion) ? $currentVersion : \DcatPage\default_version()) . '.js') }}';
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        }

        setTimeout(indices, 1);
    })();

</script>
