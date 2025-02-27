import pandas as pd
import json
import argparse
import os
from typing import Dict, Any, List, Union
from collections import defaultdict
import logging

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

def process_sheet(
    df: pd.DataFrame,
    sheet_name: str,
    university_name: str = "Universität der Künste Berlin"
) -> Dict[str, Any]:
    """
    Process a single dataframe (sheet) and convert to nested structure
    
    Parameters:
    -----------
    df : pandas.DataFrame
        The dataframe to process
    sheet_name : str
        Name of the sheet for logging and default faculty name
    university_name : str
        Name of the institution/university
        
    Returns:
    --------
    dict
        The faculty data structure

    Use:
    ----
    python excel_to_nested_json.py your_excel_file.xlsx

    with debugging enabled:
        python excel_to_nested_json.py your_excel_file.xlsx --debug


    """
    # Create a case-insensitive mapping of actual column names
    column_map = {col.lower(): col for col in df.columns}
    logging.info(f"Sheet {sheet_name} - Column map: {column_map}")
    
    # Try to find the right columns
    faculty_col = None
    institute_col = None
    course_col = None
    class_col = None
    
    for col_lower, actual_col in column_map.items():
        if "fakultät" in col_lower or "einrichtung" in col_lower:
            faculty_col = actual_col
        elif "institut" in col_lower:
            institute_col = actual_col
        elif "studiengang" in col_lower:
            course_col = actual_col
        elif "fachgebiet" in col_lower or "fachklasse" in col_lower or "klasse" in col_lower:
            class_col = actual_col
    
    logging.info(f"Sheet {sheet_name} - Detected columns: faculty={faculty_col}, institute={institute_col}, "
                 f"course={course_col}, class={class_col}")
    
    # Process faculty data
    faculty_name = sheet_name
    if faculty_col and not df[faculty_col].dropna().empty:
        # If faculty column exists and has values, try to get a single faculty name
        unique_faculties = df[faculty_col].dropna().unique()
        if len(unique_faculties) == 1:
            faculty_name = unique_faculties[0]  # Use the single faculty name
        else:
            # If multiple faculties, we'll handle that later
            pass
    
    faculty_data = {
        "name": faculty_name,
        "type": "faculty",
        "institutes": []
    }
    
    # Handle cases where faculty name comes from column:
    if faculty_col and len(df[faculty_col].dropna().unique()) > 1:
        # Multiple faculties in this sheet - process each one separately
        faculties = df[faculty_col].dropna().unique()
        
        # We'll return a list of faculty data instead of a single faculty
        faculty_data = []
        
        # Create a placeholder for items with missing faculty
        missing_faculty = {
            "name": f"{sheet_name} (unspecified faculty)",
            "type": "faculty",
            "institutes": []
        }
        
        # First, process rows with faculty values
        for faculty_name in faculties:
            current_faculty = {
                "name": faculty_name,
                "type": "faculty",
                "institutes": []
            }
            
            # Filter for this faculty
            faculty_df = df[df[faculty_col] == faculty_name]
            
            # Process institutes for this faculty
            if institute_col:
                process_institutes(faculty_df, institute_col, course_col, class_col, current_faculty)
            
            faculty_data.append(current_faculty)
        
        # Now process rows with missing faculty value
        missing_faculty_df = df[df[faculty_col].isna()]
        
        if not missing_faculty_df.empty:
            if institute_col:
                process_institutes(missing_faculty_df, institute_col, course_col, class_col, missing_faculty)
            
            # Only add if it has institutes
            if missing_faculty["institutes"]:
                faculty_data.append(missing_faculty)
        
        return faculty_data
    else:
        # Single faculty - process its institutes
        if institute_col:
            process_institutes(df, institute_col, course_col, class_col, faculty_data)
        
        return faculty_data

def process_institutes(df, institute_col, course_col, class_col, faculty_data):
    """
    Helper function to process institutes for a faculty
    Handles rows with missing institute values
    """
    # First process rows with institute values
    if institute_col in df.columns:
        institutes = df[df[institute_col].notna()][institute_col].unique()
        
        for institute_name in institutes:
            institute_data = {
                "name": institute_name,
                "type": "institute",
                "courses": []
            }
            
            # Filter for this institute
            institute_df = df[df[institute_col] == institute_name]
            
            # Process courses
            if course_col:
                process_courses(institute_df, course_col, class_col, institute_data)
            
            faculty_data["institutes"].append(institute_data)
        
        # Now process rows with missing institute value
        missing_institute_df = df[df[institute_col].isna()]
        
        if not missing_institute_df.empty and course_col:
            # Create a placeholder institute for items with missing institute
            missing_institute = {
                "name": "Unspecified Institute",
                "type": "institute",
                "courses": []
            }
            
            process_courses(missing_institute_df, course_col, class_col, missing_institute)
            
            # Only add if it has courses
            if missing_institute["courses"]:
                faculty_data["institutes"].append(missing_institute)
    else:
        # No institute column - create a default institute
        default_institute = {
            "name": "Default Institute",
            "type": "institute",
            "courses": []
        }
        
        # Process courses directly
        if course_col:
            process_courses(df, course_col, class_col, default_institute)
            
            # Only add if it has courses
            if default_institute["courses"]:
                faculty_data["institutes"].append(default_institute)

def process_courses(df, course_col, class_col, institute_data):
    """
    Helper function to process courses for an institute
    Handles rows with missing course values
    """
    # First process rows with course values
    if course_col in df.columns:
        courses = df[df[course_col].notna()][course_col].unique()
        
        for course_name in courses:
            course_data = {
                "name": course_name,
                "type": "course",
                "classes": []
            }
            
            # Filter for this course
            course_df = df[df[course_col] == course_name]
            
            # Process classes
            if class_col:
                process_classes(course_df, class_col, course_data)
            
            institute_data["courses"].append(course_data)
        
        # Now process rows with missing course value
        missing_course_df = df[df[course_col].isna()]
        
        if not missing_course_df.empty and class_col:
            # Create a placeholder course for items with missing course
            missing_course = {
                "name": "Unspecified Course",
                "type": "course",
                "classes": []
            }
            
            process_classes(missing_course_df, class_col, missing_course)
            
            # Only add if it has classes
            if missing_course["classes"]:
                institute_data["courses"].append(missing_course)
    else:
        # No course column - create a default course
        default_course = {
            "name": "Default Course",
            "type": "course",
            "classes": []
        }
        
        # Process classes directly
        if class_col:
            process_classes(df, class_col, default_course)
            
            # Only add if it has classes
            if default_course["classes"]:
                institute_data["courses"].append(default_course)

def process_classes(df, class_col, course_data):
    """Helper function to process classes for a course"""
    if class_col in df.columns:
        classes = df[class_col].dropna().unique()
        
        for class_name in classes:
            if pd.notna(class_name) and class_name.strip():  # Check if value is not empty or whitespace
                class_data = {
                    "name": class_name,
                    "type": "class"
                }
                course_data["classes"].append(class_data)
    else:
        # If there's no class column but we have rows, create a default class
        if not df.empty:
            class_data = {
                "name": "Default Class",
                "type": "class"
            }
            course_data["classes"].append(class_data)

def excel_to_nested_json(
    excel_file: str, 
    output_file: str = None,
    sheets: List[str] = None,  # Now accepts a list of sheets
    indent: int = 2,
    university_name: str = "Universität der Künste Berlin"
) -> Dict[str, Any]:
    """
    Convert Excel file to nested JSON following the hierarchy:
    institution -> faculties -> institutes -> courses -> classes
    Processes multiple sheets and combines them into one structure.
    
    Parameters:
    -----------
    excel_file : str
        Path to the Excel file
    output_file : str, optional
        Path to the output JSON file. If None, won't save to file but return the data
    sheets : list of str, optional
        Specific sheet names to convert. If None, processes all sheets
    indent : int, optional
        Number of spaces for indentation in the output JSON file
    university_name : str, optional
        Name of the institution/university
        
    Returns:
    --------
    dict
        The converted data as a nested dictionary
    """
    # Check if file exists
    if not os.path.exists(excel_file):
        raise FileNotFoundError(f"Excel file not found: {excel_file}")
    
    # Get Excel file info
    xl = pd.ExcelFile(excel_file)
    available_sheets = xl.sheet_names
    
    # Determine which sheets to process
    if sheets is None:
        sheets_to_process = available_sheets
        logging.info(f"No sheets specified. Processing all sheets: {sheets_to_process}")
    else:
        # Make sure all requested sheets exist (case-insensitive)
        available_sheets_lower = [s.lower() for s in available_sheets]
        sheets_to_process = []
        
        for sheet in sheets:
            sheet_lower = sheet.lower()
            if sheet_lower in available_sheets_lower:
                sheet_index = available_sheets_lower.index(sheet_lower)
                sheets_to_process.append(available_sheets[sheet_index])
            else:
                raise ValueError(f"Sheet '{sheet}' not found in Excel file. Available sheets: {available_sheets}")
    
    # Create the main structure
    result = {
        "name": university_name,
        "type": "institution",
        "faculties": []
    }
    
    # Process each sheet
    for sheet_name in sheets_to_process:
        logging.info(f"Processing sheet: {sheet_name}")
        df = pd.read_excel(excel_file, sheet_name=sheet_name)
        
        # Skip empty sheets
        if df.empty:
            logging.warning(f"Sheet {sheet_name} is empty, skipping")
            continue
        
        # Process the sheet
        faculty_data = process_sheet(df, sheet_name, university_name)
        
        # Add to main structure
        if isinstance(faculty_data, list):
            # Multiple faculties from this sheet
            result["faculties"].extend(faculty_data)
        else:
            # Single faculty from this sheet
            result["faculties"].append(faculty_data)
    
    # Save to file if output_file is specified
    if output_file:
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(result, f, indent=indent, ensure_ascii=False)
        logging.info(f"Nested JSON data saved to {output_file}")
    
    return result

def main():
    parser = argparse.ArgumentParser(description="Convert Excel file to nested JSON")
    parser.add_argument("excel_file", help="Path to the Excel file")
    parser.add_argument("-o", "--output", help="Path to the output JSON file")
    parser.add_argument("-s", "--sheets", nargs="+", help="Specific sheet names to convert (default: all sheets)")
    parser.add_argument("--indent", type=int, default=2, help="Number of spaces for indentation")
    parser.add_argument("--university", default="Universität der Künste Berlin", 
                      help="University/institution name")
    parser.add_argument("--debug", action="store_true", help="Enable debug logging")
    
    args = parser.parse_args()
    
    # Set logging level
    if args.debug:
        logging.getLogger().setLevel(logging.DEBUG)
    
    # If no output file is specified, use the Excel filename with .json extension
    if not args.output:
        args.output = os.path.splitext(args.excel_file)[0] + ".json"
    
    try:
        excel_to_nested_json(
            args.excel_file,
            args.output,
            args.sheets,
            args.indent,
            args.university
        )
        logging.info("Conversion completed successfully!")
    except Exception as e:
        logging.error(f"Error: {e}")
        import traceback
        logging.error(traceback.format_exc())
        return 1
    
    return 0

if __name__ == "__main__":
    exit(main())
