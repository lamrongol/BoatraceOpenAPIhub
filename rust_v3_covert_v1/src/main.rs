use serde::{Deserialize, Serialize};
use serde_json::Value;
use std::collections::BTreeMap;
use std::path::PathBuf;
use std::{env, fs};

fn main() {
    let docs_dir = {
        let mut rsrc_file = env::current_exe().expect("Can't find path to executable");
        rsrc_file.pop();
        rsrc_file.pop();
        rsrc_file.pop();

        rsrc_file.pop();
        rsrc_file.join("docs")
    };
    let v1_data_dir = docs_dir.join("v1");
    let v3_data_dir = docs_dir.join("v3");

    // //Only once
    // covert_all_files(&v3_data_dir, &v1_data_dir);
    // return;

    let recorded_file = v3_data_dir.join("recorded_day.json");
    let date = fs::read_to_string(recorded_file).expect("Can't read file").replace("-", "");
    let year = date[0..4].to_string();
    let v3_file_path = v3_data_dir.join(year.clone()).join(format!("{}.json", date));
    let v1_year_dir = v1_data_dir.join(year.clone());
    if !v1_year_dir.exists() {
        fs::create_dir(&v1_year_dir).unwrap();
    }
    let v1_file_path = v1_year_dir.join(format!("{}.json", date));
    convert_v3_to_v1(&v3_file_path, &v1_file_path);
}


//TODO 他も構造体を作らないと項目の順番がおかしくなるが、パースする分には問題ないため一旦ここのみ
#[derive(Debug, Serialize, Deserialize)]
struct Body {
    date: String,
    stadium_number: i64,
    race_number: i64,
    closed_at: Option<String>,
    grade_number_source: Option<String>,
    grade_number: Option<i64>,
    title: Option<String>,
    subtitle: Option<String>,
    distance: Option<i64>,
    day_number_source: Option<String>,
    day_number: Option<i64>,
    racers: Option<BTreeMap<i64, Value>>,
    expect: Option<Value>,
    preview: Option<Value>,
    odds: Option<Value>,
    result: Option<Value>,
}

#[derive(Debug, Serialize, Deserialize)]
struct Races {
    races: BTreeMap<i64, Body>,
}
#[derive(Debug, Serialize, Deserialize)]
struct Programs {
    stadiums: BTreeMap<i64, Races>,
}
#[derive(Debug, Serialize, Deserialize)]
struct Wrapper {
    programs: Programs,
}

fn convert_v3_to_v1(original: &PathBuf, converted_file: &PathBuf) {
    let mut raw_str = fs::read_to_string(original).unwrap();
    raw_str = raw_str.replace("\"number\"", "\"race_number\"")
        .replace("\"grade_label\"", "\"grade_number_source\"")
        .replace("\"boats\"", "\"racers\"")
        .replace("\"racer_boat_number\"", "\"entry_number\"")
        .replace("\"racer_name\"", "\"name\"")
        .replace("\"racer_number\"", "\"number\"")
        .replace("\"racer_branch_number\"", "\"branch_number\"")
        .replace("\"racer_birthplace_number\"", "\"birthplace_number\"")
        .replace("\"racer_age\"", "\"age\"")
        .replace("\"racer_weight\"", "\"weight\"")
        .replace("\"racer_flying_count\"", "\"flying_count\"")
        .replace("\"racer_late_count\"", "\"late_count\"")
        .replace("\"racer_average_start_timing\"", "\"average_start_timing\"")
        .replace("\"racer_national_top_1_percent\"", "\"national_win_rate\"")
        .replace("\"racer_national_top_2_percent\"", "\"national_top_2_percent\"")
        .replace("\"racer_national_top_3_percent\"", "\"national_top_3_percent\"")
        .replace("\"racer_local_top_1_percent\"", "\"local_win_rate\"")
        .replace("\"racer_local_top_2_percent\"", "\"local_top_2_percent\"")
        .replace("\"racer_local_top_3_percent\"", "\"local_top_3_percent\"")
        .replace("\"racer_assigned_motor_number\"", "\"motor_number\"")
        .replace("\"racer_assigned_motor_top_2_percent\"", "\"motor_top_2_percent\"")
        .replace("\"racer_assigned_motor_top_3_percent\"", "\"motor_top_3_percent\"")
        .replace("\"racer_assigned_boat_number\"", "\"boat_number\"")
        .replace("\"racer_assigned_boat_top_2_percent\"", "\"boat_top_2_percent\"")
        .replace("\"racer_assigned_boat_top_3_percent\"", "\"boat_top_3_percent\"")
        .replace("\"racer_course_number\"", "\"course_number\"")
        .replace("\"racer_start_timing\"", "\"start_timing\"")
        .replace("\"racer_weight\"", "\"weight\"")
        .replace("\"racer_weight_adjustment\"", "\"weight_adjustment\"")
        .replace("\"racer_exhibition_time\"", "\"exhibition_time\"")
        .replace("\"racer_tilt_adjustment\"", "\"tilt_adjustment\"")
        .replace("\"win_odds\"", "\"win\"")
        .replace("\"place_odds\"", "\"place\"")
        .replace("\"exacta_odds\"", "\"exacta\"")
        .replace("\"quinella_odds\"", "\"quinella\"")
        .replace("\"quinella_place_odds\"", "\"quinella_place\"")
        .replace("\"trifecta_odds\"", "\"trifecta\"")
        .replace("\"trio_odds\"", "\"trio\"")
        .replace("\"racer_course_number\"", "\"course_number\"")
        .replace("\"racer_place_number\"", "\"place_number\"")
        .replace("\"racer_number\"", "\"number\"")
        .replace("\"racer_name\"", "\"name\"")
        .replace("\"day_label\"", "\"day_number_source\"");

    let original_json: Value =
        serde_json::from_str(&raw_str).unwrap();
    let mut programs = Programs { stadiums: Default::default() };
    for original in original_json["programs"].as_array().unwrap().iter() {
        let mut body_json = original.clone();
        let stadium_number = body_json["stadium_number"].as_i64().unwrap();
        let race_number = body_json["race_number"].as_i64().unwrap();

        let stadium_obj = programs.stadiums.entry(stadium_number).or_insert(Races { races: BTreeMap::new() });

        let day_number_source_tmp = body_json["day_number_source"].as_str();
        if day_number_source_tmp.is_none() {
            body_json["racers"] = serde_json::Value::Null;
            let body: Body = serde_json::from_str(&serde_json::to_string(&body_json).unwrap()).unwrap();
            stadium_obj.races.insert(race_number, body);
            continue;
        }
        let day_number_source = day_number_source_tmp.unwrap();
        let first_char = zen2han(day_number_source.chars().next().unwrap());
        let day_number_tmp = first_char.to_string().parse::<i64>();
        let day_number = if day_number_tmp.is_ok() {
            day_number_tmp.unwrap()
        } else {
            match day_number_source.to_string().as_str() {
                "初日" => 1,
                "最終日" => 100, //TODO 最終日はとりあえずこれで表す
                _ => -1, //TODO 順延・中止などはとりあえずこれで表す
            }
        };
        body_json["day_number"] = Value::from(day_number);


        let racers = body_json["racers"].as_array().unwrap().clone();
        let mut racers_map = serde_json::Map::new();
        for racer in racers.iter() {
            let number = racer["entry_number"].as_i64().unwrap();
            racers_map.insert(number.to_string(), racer.clone());
        }
        body_json["racers"] = Value::Object(racers_map);
        //result
        {
            let racers = body_json["result"]["racers"].as_array().unwrap().clone();
            let mut racers_map = serde_json::Map::new();
            for racer in racers.iter() {
                let number = racer["entry_number"].as_i64().unwrap();
                racers_map.insert(number.to_string(), racer.clone());
            }
            body_json["result"]["racers"] = Value::Object(racers_map);
        }

        let body: Body = serde_json::from_str(&serde_json::to_string(&body_json).unwrap()).unwrap();
        stadium_obj.races.insert(race_number, body);
    }
    let wrapper = Wrapper { programs };
    let json_str = serde_json::to_string(&wrapper).unwrap();
    fs::write(&converted_file, json_str).unwrap();
}

//only once
fn covert_all_files(v3_dir: &PathBuf, v1_dir: &PathBuf) {
    for dir in fs::read_dir(v3_dir).unwrap() {
        let dir = dir.unwrap();
        if dir.file_type().unwrap().is_file() {
            continue;
        }
        let year = dir.file_name().into_string().unwrap();
        let v1_year_dir = v1_dir.join(year.clone());
        if !v1_year_dir.exists() {
            fs::create_dir(&v1_year_dir).unwrap();
        }
        let entries = fs::read_dir(dir.path()).unwrap();
        for entry in entries {
            let v3_file = entry.unwrap().path();
            let date = v3_file.file_name().unwrap().to_str().unwrap()[0..8].to_string();
            let v1_file = v1_year_dir.join(format!("{}.json", date));
            convert_v3_to_v1(&v3_file, &v1_file);
        }
    }
}

//https://qiita.com/kujirahand/items/e9e0110897af48799ce0
/// 全角英数記号を半角英数記号に変換
fn zen2han(c: char) -> char {
    match c {
        // half ascii code
        '\u{0020}'..='\u{007E}' => c,
        // FullWidth
        // '！'..='～' = '\u{FF01}'..='\u{FF5E}'
        '\u{FF01}'..='\u{FF5E}' => char_from_u32(c as u32 - 0xFF01 + 0x21, c),
        // space
        '\u{2002}'..='\u{200B}' => ' ',
        '\u{3000}' | '\u{FEFF}' => ' ',
        // others
        _ => c,
    }
}
/// u32からcharに変換
fn char_from_u32(i: u32, def: char) -> char {
    char::from_u32(i).unwrap_or(def)
}