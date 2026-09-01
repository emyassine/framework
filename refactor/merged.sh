#!/usr/bin/env bash

set -euo pipefail

output_path=""
raw_inputs=()

# Parse CLI flags
while [[ $# -gt 0 ]]; do
    case "$1" in
        --dir=*)
            raw_inputs+=("${1#*=}")
            shift
            ;;
        --dir)
            raw_inputs+=("$2")
            shift 2
            ;;
        --dirs=*)
            IFS=',' read -r -a split_dirs <<< "${1#*=}"
            raw_inputs+=("${split_dirs[@]}")
            shift
            ;;
        --dirs)
            IFS=',' read -r -a split_dirs <<< "$2"
            raw_inputs+=("${split_dirs[@]}")
            shift 2
            ;;
        --output=*|--out=*)
            output_path="${1#*=}"
            shift
            ;;
        --output|--out|-o)
            output_path="$2"
            shift 2
            ;;
        *)
            echo "Error: Unknown option '$1'" >&2
            exit 1
            ;;
    esac
done

if [ ${#raw_inputs[@]} -eq 0 ]; then
    echo "Error: No input paths specified. Use --dir=<path> or --dirs=<path1,path2>." >&2
    exit 1
fi

# Set default output path
if [ -z "$output_path" ]; then
    output_path="merged.txt"
fi

# Resolve incremental output file name if target exists
target_output="$output_path"
if [ -e "$target_output" ]; then
    parent_dir=$(dirname "$output_path")
    file_base=$(basename "$output_path")
    extension=""
    file_prefix="$file_base"

    if [[ "$file_base" == *.* ]]; then
        extension=".${file_base##*.}"
        file_prefix="${file_base%.*}"
    fi

    counter=1
    while [ -e "${parent_dir}/${file_prefix}.${counter}${extension}" ]; do
        ((counter++))
    done
    target_output="${parent_dir}/${file_prefix}.${counter}${extension}"
fi

# Ensure output target directory exists
parent_dir=$(dirname "$target_output")
mkdir -p "$parent_dir"

# Binary file detector
is_binary_file() {
    local target_file="$1"
    if [ ! -f "$target_file" ]; then
        return 0
    fi
    local mime_type
    mime_type=$(file -b --mime-encoding "$target_file" 2>/dev/null || echo "binary")
    if [ "$mime_type" = "binary" ]; then
        return 0
    fi
    return 1
}

# Filter existing valid paths
valid_paths=()
for raw_path in "${raw_inputs[@]}"; do
    clean_path=$(echo "$raw_path" | xargs)
    if [ -e "$clean_path" ]; then
        valid_paths+=("$clean_path")
    else
        echo "Warning: Path '$clean_path' does not exist. Skipping." >&2
    fi
done

if [ ${#valid_paths[@]} -eq 0 ]; then
    echo "Error: None of the provided paths exist." >&2
    exit 1
fi

# Execute aggregation
{
    echo "=================================================="
    echo "STRUCTURE"
    echo "=================================================="
    for path in "${valid_paths[@]}"; do
        if [ -d "$path" ]; then
            echo -e "\n--- Directory: $path ---"
            if command -v tree >/dev/null 2>&1; then
                tree "$path"
            else
                find "$path"
            fi
        elif [ -f "$path" ]; then
            echo -e "\n--- File: $path ---"
        fi
    done

    echo -e "\n=================================================="
    echo "FILE CONTENTS"
    echo "=================================================="

    for path in "${valid_paths[@]}"; do
        if [ -d "$path" ]; then
            while IFS= read -r -d '' item; do
                # Ignore output file if inside target dir
                real_item=$(realpath "$item" 2>/dev/null || echo "$item")
                real_target=$(realpath "$target_output" 2>/dev/null || echo "$target_output")
                if [ "$real_item" = "$real_target" ]; then
                    continue
                fi

                if is_binary_file "$item"; then
                    continue
                fi

                echo "--------------------------------------------------"
                echo ">>> FILE: $item"
                echo -e "--------------------------------------------------\n"
                cat "$item"
                echo -e "\n"
            done < <(find "$path" -type f ! -path '*/.*' -print0)
        elif [ -f "$path" ]; then
            if ! is_binary_file "$path"; then
                echo "--------------------------------------------------"
                echo ">>> FILE: $path"
                echo -e "--------------------------------------------------\n"
                cat "$path"
                echo -e "\n"
            fi
        fi
    done
} > "$target_output"

echo "Output generated: $target_output"
