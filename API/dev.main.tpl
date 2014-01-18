<div>
 <p>{{job.info.location}} | {{job.info.type}} | {{job.info.industry}} |
    {{job.info.duration}} | start {{job.info.start}}</p>
    
<ul>
{{job.summaries}}
</ul>

    <div>
    <h3>Description</h3>
    {{job.info.extended}}
    
    <p>Candidates who can offer the following experience are encouraged to apply.</p>
    <ul>
        {{job.info.skills}}
    </ul>
    </div>
    
    <h3>Salary</h3>
    <p>{{job.salary.extended}}</p>
    
    <h4>Benifiits</h4>
    <p>{{job.salary.benifits}}</p>
    
    <h3>Contact or Apply</h3>
    <p><a href="{{job.apply}}" target="_blank" class="button">Apply now</a> quoting {{job.reference}} or contact:</p>
    <p><a href="mailto:{{job.contact.email}}">{{job.contact.name}}</a><br />
        {{job.contact.phone}}<br>
        {{job.contact.email}}
    </p>
    <p>{{job.date.create}}</p>
    
    {{job.info.tags}}
    
    
    
    
    <h2>Ignore below this heading. This is to check extra fields that aren't being used are coming through</h2>
    
    id: {{job.id}}<br>
    job.salary.currency: {{job.salary.currency}}<br>
    job.salary.from: {{job.salary.from}}<br>
    job.salary.to; {{job.salary.to}}<br>
    job.salary.per: {{job.salary.per}}<br>
    job.salary.extended: {{job.salary.extended}}<br><br>
        
</div>