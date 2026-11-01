<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $display_name
 * @property mixed $npi
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $middle_name
 * @property mixed $email
 * @property mixed $phone
 * @property mixed $fax
 * @property mixed $dob
 * @property mixed $gender
 * @property mixed $provider_type
 * @property mixed $specialty
 * @property mixed $taxonomy_code
 * @property mixed $user_id
 * @property mixed $team_id
 * @property mixed $created_by
 * @property mixed $updated_by
 * @property mixed $status
 * @property mixed $notes
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'full_name' => $this->full_name,
            'npi' => $this->npi,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'dob' => $this->dob?->format('Y-m-d'),
            'gender' => $this->gender,
            'provider_type' => $this->provider_type,
            'specialty' => $this->specialty,
            'taxonomy_code' => $this->taxonomy_code,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'team' => $this->whenLoaded('team', fn () => [
                'id' => $this->team->id,
                'name' => $this->team->name,
                'email' => $this->team->email,
            ]),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ]),
            'updater' => $this->whenLoaded('updater', fn () => [
                'id' => $this->updater->id,
                'name' => $this->updater->name,
                'email' => $this->updater->email,
            ]),
            'personal_detail' => $this->whenLoaded('personalDetail', fn () => [
                'id' => $this->personalDetail->id,
                'alternate_phone' => $this->personalDetail->alternate_phone,
                'address' => $this->personalDetail->address,
                'city' => $this->personalDetail->city,
                'state' => $this->personalDetail->state,
                'zip_code' => $this->personalDetail->zip_code,
                'country' => $this->personalDetail->country,
                'languages' => $this->personalDetail->languages,
                'previous_names' => $this->personalDetail->previous_names,
            ]),
            'identifiers' => $this->whenLoaded('identifiers', fn () => $this->identifiers->map(fn ($identifier) => [
                'id' => $identifier->id,
                'identifier_type' => $identifier->identifier_type,
                'identifier_value' => $identifier->identifier_value,
                'issuing_state' => $identifier->issuing_state,
                'issue_date' => $identifier->issue_date?->format('Y-m-d'),
                'expiration_date' => $identifier->expiration_date?->format('Y-m-d'),
                'verification_status' => $identifier->verification_status,
                'verification_notes' => $identifier->verification_notes,
            ])),
            'educations' => $this->whenLoaded('educations', fn () => $this->educations->map(fn ($education) => [
                'id' => $education->id,
                'institution_name' => $education->institution_name,
                'degree' => $education->degree,
                'education_type' => $education->education_type,
                'specialty' => $education->specialty,
                'start_date' => $education->start_date?->format('Y-m-d'),
                'completion_date' => $education->completion_date?->format('Y-m-d'),
                'country' => $education->country,
                'state' => $education->state,
                'city' => $education->city,
                'verification_status' => $education->verification_status,
            ])),
            'specialties' => $this->whenLoaded('providerSpecialties', fn () => $this->providerSpecialties->map(fn ($ps) => [
                'id' => $ps->id,
                'specialty_id' => $ps->specialty_id,
                'specialty' => $ps->specialty ? [
                    'id' => $ps->specialty->id,
                    'name' => $ps->specialty->name,
                    'code' => $ps->specialty->code,
                ] : null,
                'is_primary' => $ps->is_primary,
                'subspecialty' => $ps->subspecialty,
                'is_board_certified' => $ps->is_board_certified,
                'board_certification_date' => $ps->board_certification_date?->format('Y-m-d'),
                'is_active' => $ps->is_active,
            ])),
            'locations' => $this->whenLoaded('locations', fn () => $this->locations->map(fn ($location) => [
                'id' => $location->id,
                'location_name' => $location->location_name,
                'practice_name' => $location->practice_name,
                'address' => $location->address,
                'city' => $location->city,
                'state' => $location->state,
                'zip_code' => $location->zip_code,
                'country' => $location->country,
                'phone' => $location->phone,
                'fax' => $location->fax,
                'email' => $location->email,
                'office_hours' => $location->office_hours,
                'is_primary' => $location->is_primary,
                'is_billing_address' => $location->is_billing_address,
                'effective_start_date' => $location->effective_start_date?->format('Y-m-d'),
                'effective_end_date' => $location->effective_end_date?->format('Y-m-d'),
                'is_active' => $location->is_active,
            ])),
            'hospital_affiliations' => $this->whenLoaded('hospitalAffiliations', fn () => $this->hospitalAffiliations->map(fn ($affiliation) => [
                'id' => $affiliation->id,
                'facility_name' => $affiliation->facility_name,
                'department' => $affiliation->department,
                'affiliation_type' => $affiliation->affiliation_type,
                'start_date' => $affiliation->start_date?->format('Y-m-d'),
                'end_date' => $affiliation->end_date?->format('Y-m-d'),
                'status' => $affiliation->status,
                'contact_phone' => $affiliation->contact_phone,
                'contact_email' => $affiliation->contact_email,
            ])),
            'contacts' => $this->whenLoaded('contacts', fn () => $this->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'contact_name' => $contact->contact_name,
                'job_title' => $contact->job_title,
                'organization' => $contact->organization,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'contact_type' => $contact->contact_type,
                'preferred_method' => $contact->preferred_method,
                'notes' => $contact->notes,
            ])),
            'insurances' => $this->whenLoaded('insurances', fn () => $this->insurances->map(fn ($insurance) => [
                'id' => $insurance->id,
                'carrier' => $insurance->carrier,
                'policy_number' => $insurance->policy_number,
                'coverage_type' => $insurance->coverage_type,
                'per_claim_amount' => $insurance->per_claim_amount,
                'aggregate_amount' => $insurance->aggregate_amount,
                'effective_date' => $insurance->effective_date?->format('Y-m-d'),
                'expiration_date' => $insurance->expiration_date?->format('Y-m-d'),
                'agent_contact' => $insurance->agent_contact,
                'agent_phone' => $insurance->agent_phone,
                'verification_status' => $insurance->verification_status,
            ])),
            'employments' => $this->whenLoaded('employments', fn () => $this->employments->map(fn ($employment) => [
                'id' => $employment->id,
                'employer' => $employment->employer,
                'position' => $employment->position,
                'employment_type' => $employment->employment_type,
                'address' => $employment->address,
                'start_date' => $employment->start_date?->format('Y-m-d'),
                'end_date' => $employment->end_date?->format('Y-m-d'),
                'is_current' => $employment->is_current,
                'gap_explanation' => $employment->gap_explanation,
                'verification_status' => $employment->verification_status,
            ])),
            'references' => $this->whenLoaded('references', fn () => $this->references->map(fn ($reference) => [
                'id' => $reference->id,
                'reference_name' => $reference->reference_name,
                'organization' => $reference->organization,
                'designation' => $reference->designation,
                'relationship' => $reference->relationship,
                'email' => $reference->email,
                'phone' => $reference->phone,
                'reference_date' => $reference->reference_date?->format('Y-m-d'),
                'verification_status' => $reference->verification_status,
                'notes' => $reference->notes,
            ])),
            'disclosures' => $this->whenLoaded('disclosures', fn () => $this->disclosures->map(fn ($disclosure) => [
                'id' => $disclosure->id,
                'disclosure_question_id' => $disclosure->disclosure_question_id,
                'response' => $disclosure->response,
                'explanation' => $disclosure->explanation,
                'review_status' => $disclosure->review_status,
                'reviewed_by' => $disclosure->reviewer?->name,
                'reviewed_at' => $disclosure->reviewed_at?->toIso8601String(),
                'updated_at' => $disclosure->updated_at?->toIso8601String(),
            ])),
            'documents' => $this->whenLoaded('documents', fn () => ProviderDocumentResource::collection($this->documents)),
            'status' => $this->status,
            'notes' => $this->notes,
            'applications_count' => $this->whenCounted('applications'),
            'licenses_count' => $this->whenCounted('licenses'),
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get additional meta data that should be included.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
