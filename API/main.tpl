<div>
 <p>{{job.info.location}} | {{job.info.type}} | {{job.info.industry}} |
    {{job.info.duration}} | start {{job.info.start}}</p>
    
<ul>
{{job.summaries}}
</ul>

    <div>
    <h3>Description</h3>
    {{job.info.extended}}
    
    </div>
    
    <h3>Salary</h3>
    <p>{{job.salary.extended}}</p>
    
    <h4>Benefits</h4>
    <p>{{job.salary.benifits}}</p>
    
    <h3>Contact or Apply</h3>
    <p><a href="{{job.apply}}" target="_blank" class="button">Apply now</a> quoting 	{{job.reference}} or contact:</p>
    <p><a href="mailto:{{job.contact.email}}">{{job.contact.name}}</a><br />
        {{job.contact.phone}}<br>
        {{job.contact.email}}
    </p>
    <p>{{job.date.create}}</p>
    
    {{job.info.tags}}
        
</div>