export default {
    plugins: {
        'postcss-preset-env': {
            stage: 3,
            autoprefixer: {
                grid: 'autoplace',
            },
            features: {
                'nesting-rules': false,
            },
        },
    },
};
