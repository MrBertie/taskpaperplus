<?php
namespace tpp;
use \Enhance\Assert as Assert;

class TestParser extends \Enhance\TestFixture {

    function setUp() {
        $this->parser = new storage\Parser();
        $this->builder = new model\ContentBuilder();
    }

    function parse_title_with_index_and_note() {
        $title = "== 1:This is the title ==\n..\ntitle note\n..";
        $ast = $this->parser->parse($title);
        $result = $this->builder->build($ast);
        Assert::isTrue($result->title == 'This is the title');
        Assert::isTrue($result->index == 3);
        Assert::isTrue($result->note->text == 'title note');
    }

    function parse_project_with_index_and_note() {
        $project = "This is a project:\n..\nproject note\n..";
        $ast = $this->parser->parse($project);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->text == 'This is a project');
        Assert::isTrue($res->type == 'project');
        Assert::isTrue($res->note->text == 'project note');
        Assert::isTrue($res->index == 1);

    }

    function parse_task_with_no_space_afer_dash() {
        $task = "-this is a task";
        $ast = $this->parser->parse($task);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->text == 'this is a task');
    }

    function parse_task_with_done_action_date_tags_and_note() {
        $task = "- this is a task #tag #tag2 #12-dec-2013 *\n..\ntask note\n..";
        $ast = $this->parser->parse($task);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->type == 'task');
        Assert::isTrue($res->text == 'this is a task');
        Assert::isTrue($res->done == false);
        Assert::isTrue($res->action == 1);
        Assert::isTrue($res->note->text == 'task note');
        Assert::isTrue($res->tags == array('tag', 'tag2'));
        Assert::isTrue($res->date == 1386806400);
    }

    function parse_info_with_note() {
        $info = "This is info\n..\ninfo note\n..";
        $ast = $this->parser->parse($info);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->text == 'This is info');
        Assert::isTrue($res->type == 'info');
        Assert::isTrue($res->note->text == 'info note');
    }

    function parse_multiline_note_syntax() {
        $info = "This is info\n..\ninfo note\n..";
        $ast = $this->parser->parse($info);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->note->text == 'info note');
    }

    function parse_indented_note_syntax() {
        $info = "This is info\n    info note";
        $ast = $this->parser->parse($info);
        $result = $this->builder->build($ast);
        $res = $result->parsed_items['010'];
        Assert::isTrue($res->note->text == 'info note');
    }

    function parse_project_as_first_line_is_not_title() {
        $title = 'A standard Project:';
        $ast = $this->parser->parse($title);
        $result = $this->builder->build($ast);
        Assert::isTrue(empty($result->title));
        Assert::isTrue(empty($result->index));
    }

//    function parseSampleTasks() {
//        $sample = file_get_contents('tests/data/sample tasks.txt');
//        $ast= $this->parser->parse($sample);
//        $result = $this->builder->build($ast);
//        //file_put_contents('tests/data/sample-tasks', serialize($result));
//        $correct = unserialize(file_get_contents('tests/data/sample-tasks'));
//        Assert::isTrue($result == $correct);
//    }
}