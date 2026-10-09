<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Investor;
use App\Models\Client;
use App\Helpers\ActivityLogger;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('id', 'desc')->get();
        $clients = Client::orderBy('company_name', 'asc')->get();
        return view('projects.index', compact('projects', 'clients'));
    }

    public function create()
    {
        return redirect()->route('projects.index');
    }

    public function checkName(Request $request)
    {
        $projectName = trim($request->input('project_name', ''));
        $exists = Project::where('project_name', $projectName)->exists();
        return response()->json(['status' => $exists ? 'exists' : 'available']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required',
            'company_name' => 'required',
        ]);

        $project_name = trim($request->input('project_name'));
        $company_name = $request->input('company_name');
        $estimated_cost = (float)$request->input('estimated_cost', 0);
        $estimated_value = (float)$request->input('estimated_value', 0);
        $address = $request->input('address', '');
        $date = $request->input('date', date('Y-m-d'));
        $note = $request->input('note', '');

        if (Project::where('project_name', $project_name)->exists()) {
            return redirect()->back()->with('error', 'Project Name already exists!');
        }

        Investor::create([
            'Source' => $project_name,
            'investor_name' => $project_name,
            'address' => $address,
            'date' => $date,
            'note' => $note,
            'status' => 2,
        ]);

        Project::create([
            'company_name' => $company_name,
            'project_name' => $project_name,
            'estimated_cost' => $estimated_cost,
            'estimated_value' => $estimated_value,
            'address' => $address,
            'date' => $date,
            'note' => $note,
            'project_status' => 'Runing',
        ]);

        ActivityLogger::log('Create', "Added new project: {$project_name}");

        return redirect()->route('projects.index')->with('success', 'Project added successfully.');
    }

    public function show($id)
    {
        return $this->edit($id);
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $clients = Client::orderBy('company_name', 'asc')->get();
        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->update([
            'company_name' => $request->input('company_name'),
            'project_name' => $request->input('project_name'),
            'estimated_cost' => (float)$request->input('estimated_cost', 0),
            'estimated_value' => (float)$request->input('estimated_value', 0),
            'address' => $request->input('address'),
            'date' => $request->input('date'),
            'project_status' => $request->input('project_status', 'Runing'),
            'note' => $request->input('note'),
        ]);

        ActivityLogger::log('Update', "Updated project: {$project->project_name}");

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $name = $project->project_name;
        $project->delete();

        ActivityLogger::log('Delete', "Deleted project: {$name}");

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
