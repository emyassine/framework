#!/usr/bin/env bash

set -euo pipefail

# Terminal colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Dynamic terminal width detection
get_term_width() {
    tput cols 2>/dev/null || echo 80
}

# Print header and dynamic separator
print_banner() {
    local width
    width=$(get_term_width)
    echo -e "${CYAN}//> Merge Files and Folders.${NC}"
    echo -e "${CYAN}//> (c) 2025 - 2027 Numerimondes, El Moumen Yassine${NC}"
    echo -e "${CYAN}//> Yassine El Moumen <yassine@numerimondes.com>${NC}"
    echo -e "${CYAN}// Licensed under the Eclipse Public License 2.0 (EPL-2.0).${NC}"
    echo -e "${BLUE}$(printf '%.0s=' $(seq 1 "$width"))${NC}\n"
}

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
            echo -e "${RED}Error: Unknown option '$1'${NC}" >&2
            exit 1
            ;;
    esac
done

# Print banner at top of terminal execution
print_banner

# Interactive prompt if no inputs provided via CLI flags
if [ ${#raw_inputs[@]} -eq 0 ]; then
    echo -e "${BOLD}${YELLOW}Interactive Mode Enabled${NC}"
    echo -e "Enter file or directory paths below (use ${BOLD}Tab${NC} for autocompletion, left/right arrows to edit)."
    echo -e "Leave prompt empty and press ${BOLD}Enter${NC} when done.\n"

    # Configure readline options for bash read
    bind 'set completion-ignore-case on' 2>/dev/null || true

    path_index=1
    while true; do
        read -e -p "$(echo -e "${BOLD}${GREEN}Path #${path_index}:${NC} ")" input_entry
        input_entry=$(echo "$input_entry" | xargs)

        if [ -z "$input_entry" ]; then
            if [ ${#raw_inputs[@]} -gt 0 ]; then
                break
            else
                echo -e "${RED}Error: You must provide at least one valid path.${NC}"
                continue
            fi
        fi

        eval expanded_entry="$input_entry"

        if [ -e "$expanded_entry" ]; then
            raw_inputs+=("$expanded_entry")
            ((path_index++))
        else
            echo -e "${YELLOW}Warning: Path '$input_entry' does not exist. Try again.${NC}"
        fi
    done

    if [ -z "$output_path" ]; then
        echo ""
        read -e -i "merged.txt" -p "$(echo -e "${BOLD}${GREEN}Output target path:${NC} ")" user_output
        output_path=$(echo "$user_output" | xargs)
    fi
fi

# Default fallback output name
if [ -z "$output_path" ]; then
    output_path="merged.txt"
fi

# Calculate incremental output file name if file exists
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

# Ensure output parent directory exists
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

# Clean and validate input paths
valid_paths=()
for raw_path in "${raw_inputs[@]}"; do
    clean_path=$(echo "$raw_path" | xargs)
    eval clean_path="$clean_path"
    if [ -e "$clean_path" ]; then
        valid_paths+=("$clean_path")
    else
        echo -e "${YELLOW}Warning: Path '$clean_path' does not exist. Skipping.${NC}" >&2
    fi
done

if [ ${#valid_paths[@]} -eq 0 ]; then
    echo -e "${RED}Error: None of the provided target paths exist.${NC}" >&2
    exit 1
fi

echo -e "${BLUE}Merging contents into target file...${NC}"

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

term_width=$(get_term_width)
echo -e "${GREEN}Output successfully written to: ${BOLD}${target_output}${NC}"
echo -e "${BLUE}$(printf '%.0s=' $(seq 1 "$term_width"))${NC}"
