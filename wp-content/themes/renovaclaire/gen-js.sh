#!/bin/bash

# Define the directories and output files
js_dir="./js"
dist_dir="./dist"
last_mod_file="./dist/last_js"

mkdir -p $dist_dir

output_file="./dist/scripts.js"

# Get the latest modification time in the js_dir
latest_mod=$(find $js_dir -type f -name '*.js' -exec stat -c '%Y' {} \; | sort -n | tail -1)

# Check if the last_mod_file exists and compare the modification times
if [[ -f $last_mod_file ]]; then
    last_mod=$(cat $last_mod_file)
    if [[ $latest_mod -le $last_mod ]]; then
        echo "No new changes in JS files."
        exit 0
    fi
fi

rm ./dist/scripts*.js* -f
echo "Minify JS"
uglifyjs $(find $js_dir -type f) -o $output_file --beautify
hash=$(sha256sum "$output_file" | cut -c 1-10)

file_name="scripts-$hash.min.js"
final_file="./dist/$file_name"
rev_file="./dist/rev-js.json"
uglifyjs $(find $js_dir -type f) -o $final_file --source-map
echo "{\"scripts.js\":\"$file_name\"}" > $rev_file 

# Save the latest modification time
echo $latest_mod > $last_mod_file
