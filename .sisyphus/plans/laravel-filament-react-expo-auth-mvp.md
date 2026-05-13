# Laravel + Filament + React/Expo CRM Nông nghiệp - Work Plan

## TL;DR

> **Quick Summary**: Xây dựng 1 backend Laravel (PostgreSQL, Sanctum) với Filament Admin + REST API, ưu tiên một MVP nucleus chạy được end-to-end trước khi mở rộng React web/Expo/AI. Trọng tâm là dữ liệu sản xuất đúng, planning engine đáng tin, traceability dựa trên thực tế, và TDD có evidence.
>
> **Deliverables**:
> - Kiến trúc dữ liệu lõi xuyên suốt (schema + quan hệ + trạng thái)
> - Luồng MVP end-to-end tối thiểu: Nhu cầu -> Kế hoạch -> Lứa -> Nhật ký -> Thu hoạch -> Đóng gói -> QR
> - Auth + RBAC full role v1
>
> **Estimated Effort**: XL
> **Parallel Execution**: LIMITED - chỉ song song hóa phần không chặn domain nucleus
> **Critical Path**: Data Foundation -> Auth/RBAC -> Planning Engine -> Batch Lifecycle -> Ops Logs -> Harvest/Packing/QR -> UAT Slice -> Delivery/Returns/Reporting v1

---

## Context

### Original Request
User muốn dùng Laravel Filament làm admin + API, app dùng React.js + Expo; 1 backend; Sanctum; full role; triển khai chia phần nhưng DB plan xuyên suốt; PostgreSQL; TDD.
Nguồn yêu cầu chuẩn được chốt: `.docs/AgriOps — Hệ thống quản lý sản xuất, cung ứng và truy xuất nông nghiệp.md`.

### BRD Canonical Mapping (khóa tham chiếu)
- Canonical business narrative: `.docs/AgriOps — Hệ thống quản lý sản xuất, cung ứng và truy xuất nông nghiệp.md`.
- Canonical expanded section map (khi cần section chi tiết 12B/28/31): `.docs/yeu_cau_crm_nong_nghiep_v1.md`.
- Quy tắc: nếu task viện dẫn section không có trong AgriOps, phải ghi rõ “mapped from yeu_cau_crm_nong_nghiep_v1.md”.

### Interview Summary
- Chốt 1 backend Laravel thống nhất.
- API phục vụ cả React web và Expo.
- Auth strategy: Laravel Sanctum.
- Role scope: full role theo BRD v1.
- Execution: chia wave/phase nhưng giữ logic dữ liệu xuyên suốt.
- DB: PostgreSQL.
- Test strategy: TDD.

### Metis Review (addressed)
- Khóa guardrail chống scope creep bằng “MVP nucleus” rõ ràng.
- Bổ sung acceptance criteria executable cho từng khối.
- Chốt rõ non-goals theo BRD v1.

### PM/CTO Review Update (2026-05-12)
- Plan gốc bám BRD đúng nhưng phạm vi MVP quá rộng nếu triển khai đồng thời 18 task.
- Siết MVP quanh một vertical slice có giá trị: nhập nhu cầu -> tính kế hoạch -> tạo lứa -> ghi nhận thực tế -> thu hoạch -> đóng gói -> QR công khai.
- React web, Expo app, costing dashboard, alerts nâng cao, delivery/returns đầy đủ được giữ trong plan nhưng chuyển thành phase sau khi API/domain nucleus ổn định.
- Task 4 Planning Engine là blocker tuyệt đối cho Task 5-12; không mở rộng lifecycle/ops/harvest khi planning formula chưa có test xanh.

### AI-Ready Direction (future, not MVP scope creep)
- Sau MVP, AI có thể hỗ trợ: gợi ý kế hoạch trồng, phân tích lệch định mức, tóm tắt nhật ký, cảnh báo rủi ro, hỏi đáp nội bộ, gợi ý cập nhật SOP/định mức sau vụ.
- MVP hiện tại chỉ chuẩn bị nền: dữ liệu có cấu trúc, audit/event log, permission rõ, API contract ổn định, tài liệu/spec đồng bộ.
- Không để AI tự động duyệt, tự sửa định mức, tự chuyển trạng thái, hoặc tự công bố QR/public data khi chưa có người có quyền xác nhận.

---

## Work Objectives

### Core Objective
Triển khai nền tảng CRM + quản lý sản xuất nông nghiệp theo chuỗi nghiệp vụ thực tế, ưu tiên dữ liệu nhất quán xuyên suốt và khả năng cải tiến định mức sau vụ.

### Concrete Deliverables
- Laravel monolith: Filament Admin + REST API (PostgreSQL).
- RBAC cho full role v1.
- Data model core theo BRD section 28 + lifecycle section 12B.
- Mobile-ready API contract cho Expo (task, log, upload ảnh, sync trạng thái); app Expo triển khai sau API nucleus.
- Public QR traceability page.
- 19 planning/domain/business/UX/AI-ready/QA/database/ops artifacts bắt buộc:
  1) Canonical ERD + key constraints/indexes
  2) Lifecycle transition spec (Batch/Task/Approval)
  3) RBAC permission matrix (resource x action x role x condition)
  4) API contract pack v1 (auth modes, error schema, conventions)
  5) Public QR data-classification whitelist
  6) UAT scenario catalog by role
  7) Planning formula spec (input assumptions, units, rounding, missing-norm errors)
  8) Domain services map (service ownership + invariants)
  9) MVP vertical-slice checklist (demand -> QR)
  10) UX workflow map by role (default path, minimum clicks/taps, bulk actions, empty/error states)
  11) AI readiness checklist (data quality, event log, permission boundaries, human approval gates)
  12) QA/QC traceability matrix (BRD requirement -> task -> test -> evidence -> release gate)
  13) Database architecture plan (schema modules, constraints, indexes, migration/versioning, audit/event, retention)
  14) Security & privacy plan (farm scope, token policy, upload security, QR public boundary)
  15) Deployment & operations plan (environments, CI/CD, queue/scheduler, backup/restore, rollback)
  16) Offline sync contract (idempotency, retry, conflict handling, sync states)
  17) Import/export strategy (CSV/XLSX preview, validation, error mapping, report export)
  18) Performance & scale assumptions (volume estimates, query budgets, storage growth, public QR SLA)
  19) Business architecture guardrails (season/climate, soil history, multi-harvest, shortage, quality taxonomy, override governance)
  20) Multi-agent coordination board (`.sisyphus/agent-board.md`)

### Definition of Done
- [x] Tất cả migration chạy PASS trên PostgreSQL.
- [x] Bộ test domain + feature + API PASS.
- [x] MVP vertical slice demand -> QR có evidence tự động cho happy/error path.
- [x] Không mở rộng React web/Expo/costing/alerts nâng cao trước khi Task 4-11 pass theo invariant list.

### Must Have
- DB schema không hard-code sai 3 quan hệ critical:
  1) 1 lứa trồng có thể nhiều lô/luống
  2) 1 lô đóng gói có thể nhiều nguồn thu hoạch
  3) Kế hoạch và thực tế tách dữ liệu

### Must NOT Have (Guardrails)
- Không làm thành CRM khách hàng thuần.
- Không auto-update định mức khi chưa duyệt.
- Không nhồi module kho chi tiết/công nợ vào MVP.
- Không ưu tiên UI client riêng khi domain/API core chưa ổn định.
- Không để business invariant chỉ nằm trong Filament form/controller; phải nằm trong domain service/model.
- Không cho AI tự thực thi hành động nhạy cảm; AI chỉ đề xuất, người có quyền duyệt mới ghi thay đổi.

### MVP Milestones
- **MVP-0 Backend Nucleus**: T1-T4. Schema/auth/master-data/planning formula chạy xanh trên PostgreSQL.
- **MVP-1 Production Trace Slice**: T5-T11. Demand -> plan -> batch -> allocation -> work log -> harvest -> packing lot -> public QR.
- **MVP-2 Business Reality**: T12-T14. Delivery/return, costing/margin, alerts/notifications ở mức đủ báo cáo.
- **MVP-3 Client Integration**: T15-T16. React web và Expo dùng API contract đã ổn định.
- **MVP Hardening**: T17-T18 + Final Verification. Test factories, UAT, compliance, QA, scope fidelity.

### Domain Service Ownership
- `PlanningCalculationService`: demand/contract -> raw harvest -> plants -> m2 -> batches -> labor/water/cost estimate.
- `PlantingBatchLifecycleService`: trạng thái lứa, allowed transitions, audit log.
- `AllocationService`: phân bổ lứa vào plot/bed, kiểm tra diện tích, trạng thái đất, conflict.
- `WorkTaskService`: sinh task từ stage template, assign/accept/complete, tách kế hoạch và thực tế.
- `HarvestEligibilityService`: kiểm tra nghiệm thu trước thu hoạch và isolation guard.
- `PackingLotService`: validate nguồn harvest, mixing nhiều nguồn, tạo trace graph.
- `TraceabilityPresenter`: whitelist dữ liệu QR public, tuyệt đối không lộ thuốc/liều lượng/user nội bộ/chi phí.

### System Completeness Upgrade Pack
- **Data Dictionary**: chuẩn hóa field, enum, đơn vị đo, required/optional, owner module.
- **State Machine Spec**: plot, planting batch, work task, approval, harvest, packing, delivery.
- **RBAC Matrix**: role x resource x action x condition, gồm cả action nhạy cảm và approval gate.
- **API Contract v1**: error schema, pagination/filter/sort, idempotency key, upload contract, versioning.
- **Audit/Event Log Contract**: actor, action, entity, old/new values, reason, timestamp, request_id.
- **Media Storage Contract**: ảnh nhật ký/thu hoạch/giao hàng, thumbnail, access policy, retention.
- **Demo Seed + Smoke Test**: một farm/crop/demand/batch/harvest/packing/QR chạy được tự động.
- **Operational Baseline**: health check, backup/restore note, job failure logging, domain error logging.

### Business Architecture Guardrails
- **Season & climate planning factors**: planning formula phải xét season/crop cycle/climate_zone khi dữ liệu có sẵn; nếu thiếu thì dùng default rõ ràng và trả assumptions trong response.
- **Soil history & crop rotation rules**: allocation phải đọc lịch sử đất/crop family/last batch/rest period/incidents để cảnh báo hoặc chặn theo rule cấu hình.
- **Demand fulfillment & shortage handling**: planning không chỉ tính tài nguyên cần có; phải trả trạng thái đủ/thiếu đất, thiếu cây, thiếu nhân công, ngày giao rủi ro, và shortage warnings.
- **Multi-harvest batch model**: một planting batch có thể có nhiều harvest lots theo nhiều ngày/đợt; yield actual phải cộng dồn theo batch và so với plan.
- **Quality standard + reject reason taxonomy**: grade A/B/C/reject phải map với product standard và reject reason canonical để báo cáo chất lượng/return có ý nghĩa.
- **Override governance matrix**: mọi override nghiệp vụ nhạy cảm phải có role được phép, condition, reason bắt buộc, audit, và expiry/review nếu phù hợp.
- **Material usage without full inventory**: MVP không làm ledger kho chi tiết, nhưng vẫn phải ghi usage/cost snapshot cho giống/phân/thuốc/sinh học/nước/nhân công khi liên quan planning/report.
- **Customer/contract baseline**: demand/contract cần customer/channel, delivery cadence, tolerance, price snapshot hoặc pricing reference tối thiểu để không mất ngữ cảnh thương mại.
- **Return feedback loop**: return reason phải map về packing lot/source harvest/batch nếu có thể để phục vụ báo cáo chất lượng và cải tiến định mức.

### Security & Privacy Plan
- **Farm scope / tenant isolation**: mọi query nghiệp vụ phải filter theo farm scope hoặc role system; policy test bắt buộc cho user farm A không đọc/ghi dữ liệu farm B.
- **Role + condition-based authorization**: RBAC không chỉ role; phải xét farm membership, resource ownership, trạng thái nghiệp vụ, và approval condition.
- **Sanctum token policy**: token mobile/web phải có expiry/revoke path; login throttling; logout revoke; `/api/v1/me` trả scope rõ.
- **Sensitive action protection**: approve/reject, update norm, harvest override, packing publish, delivery confirmation cần policy + audit + confirm.
- **Public QR boundary**: public endpoint không dùng auth nhưng chỉ trả whitelist; không trả internal IDs nhạy cảm, user nội bộ, thuốc/liều lượng, chi phí, margin.
- **Upload security**: validate MIME/extension/size, lưu private mặc định, thumbnail/public derivative nếu cần, metadata checksum, không tin client filename.
- **API abuse controls**: rate limit cho login, public QR, upload, planning calculate; log 4xx/5xx bất thường.
- **Privacy classification**: Data Dictionary phải đánh dấu public/internal/sensitive cho field quan trọng.

### Deployment & Operations Plan
- **Environments**: local/dev cho TDD nhanh; staging PostgreSQL cho evidence; production cấu hình riêng, không dùng seed test.
- **CI/CD quality gates**: composer install, lint/static check nếu có, `php artisan test`, migration dry-run/fresh trên CI DB, route/config cache smoke.
- **Runtime services**: queue worker, scheduler, cache, storage disk, mail/push future phải có owner/config note.
- **Environment config**: `.env.example` phải đủ biến DB, Sanctum, queue, storage, app URL, public QR URL.
- **Backup/restore**: backup DB + media metadata; restore drill tối thiểu trên staging trước release.
- **Rollback strategy**: non-destructive migrations preferred; release note phải ghi migration rollback/backfill risk.
- **Operational logging**: domain errors, job failures, upload failures, auth failures, and QR public 5xx phải log có request_id.
- **Health checks**: app health, DB connectivity, queue health, storage write/read check.

### Offline Sync Contract
- **Idempotency**: mọi submit từ mobile dễ retry như accept task, create log, upload photo, sync completion phải hỗ trợ idempotency key/client_uuid.
- **Client sync states**: pending, uploading, synced, failed, conflict; API response phải đủ để UI hiển thị trạng thái rõ.
- **Retry policy**: retry upload/log an toàn, không tạo duplicate; server phải detect duplicate bằng client_uuid/idempotency key.
- **Conflict handling**: nếu task đã bị hủy/đổi trạng thái khi offline, server trả conflict code + current state + next allowed actions.
- **Media sync**: log có thể tạo draft/pending media; upload ảnh có checksum/size/MIME; retry không làm mất log.
- **Clock handling**: client_time và server_time lưu riêng cho field logs; server_time là chuẩn cho audit.
- **MVP scope**: MVP-1 chỉ cần API contract và server idempotency; Expo offline UI đầy đủ ở MVP-3.

### Import/Export Strategy
- **Import targets**: farm/plot/bed, crop/variety, norms/profiles, product standards, initial users/roles.
- **Preview before commit**: import CSV/XLSX phải có bước preview, validate, show row errors; không ghi partial mơ hồ.
- **Error mapping**: lỗi import phải chỉ rõ row, column, value, reason, suggested fix.
- **Idempotent imports**: dùng natural key/code để update hoặc skip có kiểm soát; không tạo duplicate code.
- **Export targets**: production plan, task list, harvest/packing lot, QR list, UAT/report snapshots.
- **MVP scope**: import/export không chặn MVP-1 nếu seed canonical đủ, nhưng phải có strategy trước khi onboarding dữ liệu thật.

### Performance & Scale Assumptions
- **Initial volume assumption**: vài farm, hàng chục plots/beds, hàng trăm tasks/logs/tháng, ảnh nhật ký tăng đều; dùng để chọn index/storage trước MVP.
- **Critical query budgets**: task list theo role/farm/status/date < 500ms trên staging seed; QR public < 2s; planning calculate < 2s khi định mức đầy đủ.
- **Storage growth**: ảnh nhật ký/thu hoạch/giao hàng phải có size limit, thumbnail strategy, retention note.
- **Dashboard strategy**: MVP dashboard dùng query/index trực tiếp; nếu chậm mới tạo snapshots/materialized views.
- **Public QR resilience**: endpoint QR không phụ thuộc tác vụ nặng runtime; tránh N+1 queries; cache nếu cần sau khi privacy test pass.
- **Load test trigger**: khi vượt volume giả định hoặc QR public dùng thật, thêm smoke/perf test cho QR và task list.

### Database Architecture Plan
- **PostgreSQL as source of truth**: PostgreSQL là DB chuẩn cho migration, FK, check constraints, indexes, query plan; SQLite chỉ dùng nếu cần dev nhanh và không được xem là evidence cuối.
- **Schema module boundaries**:
  - Identity/RBAC: users, roles/permissions hoặc role enum, farm membership/scope.
  - Master data: farms, plots, beds, crops, varieties, standards, growth stages, norms/profiles.
  - Planning: demands/contracts, production plans, planning snapshots, formula assumptions.
  - Production lifecycle: planting batches, batch allocations, work tasks, farming logs.
  - Safety/control: incidents, chemical/biological usages, isolation windows, inspections, approvals.
  - Post-harvest: harvest lots, processing records, packing lots, packing sources, QR codes.
  - Commercial/reporting: deliveries, returns, costs, price tables, report snapshots.
  - Platform: media, audit logs, domain events, jobs/outbox, AI suggestions future.
- **Canonical data dictionary**: mọi table/column quan trọng phải có owner module, type, unit, enum source, nullable rule, default, index need, privacy classification.
- **Enum/state strategy**: trạng thái nghiệp vụ dùng enum canonical trong code + DB check constraint hoặc lookup table; không lưu free text cho status.
- **Critical relationship constraints**:
  - batch allocations phải hỗ trợ một lứa nhiều plot/bed và không orphan.
  - packing sources phải hỗ trợ một packing lot nhiều harvest lots, nhiều farm/source.
  - actual records phải tham chiếu plan/batch/task nhưng không ghi đè plan snapshot.
  - quantity fields phải có unit hoặc dùng typed columns rõ nghĩa.
- **Index strategy**:
  - FK indexes cho mọi relationship lớn.
  - Composite indexes cho các filter thường dùng: farm_id + status, batch_id + status, due_date + status, crop_id + variety_id, packing_lot_id + harvest_lot_id.
  - Unique constraints cho code nghiệp vụ: farm code, plot code trong farm, bed code trong plot, batch code, harvest lot code, packing lot code, QR code.
  - Partial indexes cho queue/task trạng thái mở nếu volume tăng.
- **Audit/event model**:
  - audit_logs lưu actor_id, action, entity_type, entity_id, old_values, new_values, reason, request_id, occurred_at.
  - domain_events lưu event_type, aggregate_type/id, payload, occurred_at, processed_at để phục vụ alert/report/AI sau này.
  - thay đổi nhạy cảm: approve/reject, transition, update norm, harvest eligibility override, packing, delivery, public QR publish.
- **Snapshot strategy**:
  - production plan lưu formula version và assumptions tại thời điểm tính.
  - delivery/revenue/cost lưu price/cost snapshot để báo cáo không đổi khi bảng giá đổi.
  - QR public có thể dùng presenter live từ thực tế, nhưng privacy whitelist phải có test snapshot.
- **Migration/versioning strategy**:
  - migration phải chạy xanh trên PostgreSQL staging trước khi mark done.
  - không destructive change nếu chưa có backfill/compatibility note.
  - mọi enum/state mới cần migration + code mapping + test.
  - seed canonical và negative seed chạy được sau migrate fresh.
- **Data lifecycle & retention**:
  - media file có metadata DB, storage path, MIME, size, checksum, visibility, retention policy.
  - audit/event logs giữ lâu hơn operational logs; không xóa dữ liệu traceability khi QR còn public.
  - soft delete chỉ dùng cho master data ít rủi ro; record traceability/actual nên dùng status/cancel record thay vì xóa mềm tùy tiện.
- **Reporting/read model plan**:
  - MVP dùng query trực tiếp có index tốt.
  - Khi volume tăng, tạo report snapshots/materialized views cho yield, loss, cost, margin, SLA.
  - Không tối ưu sớm bằng data warehouse trước khi MVP có dữ liệu thực.
- **AI-ready DB foundation**:
  - AI suggestions lưu riêng: suggestion_type, entity context, input summary, recommendation, confidence, status, reviewer_id, decision_reason.
  - Không để AI ghi trực tiếp vào bảng chuẩn; chỉ ghi suggestion/draft chờ duyệt.

### QA/QC Strategy
- **Requirement traceability**: mọi requirement chính trong BRD phải map tới task, test case, evidence file, và trạng thái pass/fail.
- **Risk-based testing**: ưu tiên test sâu cho planning formula, state transition, RBAC, isolation guard, packing mixing, QR privacy, upload/log mobile.
- **Test pyramid**: unit test cho formula/state machine; feature/API test cho workflow; smoke/E2E cho demand -> QR; manual exploratory cho UX và edge case nghiệp vụ.
- **Acceptance criteria executable**: mỗi task phải có pass/fail đo được, không dùng tiêu chí mơ hồ như “hoạt động ổn”.
- **Regression pack**: mọi bug nghiêm trọng khi fix phải có regression test hoặc smoke script tương ứng.
- **Evidence discipline**: log/screenshot/API response phải lưu vào `.sisyphus/evidence/` theo đúng scenario; evidence phải đủ để người khác replay.
- **Defect lifecycle**: New -> Triaged -> In Progress -> Fixed -> QA Verify -> Closed/Reopened.
- **Release gate**: không release nếu còn blocker/critical, nếu thiếu evidence cho invariant bắt buộc, hoặc nếu QR public lộ dữ liệu cấm.

### QA/QC Severity Model
- **Blocker**: mất dữ liệu, migration fail, login toàn hệ thống hỏng, tạo QR sai nguồn, lộ dữ liệu cấm public, cập nhật định mức không qua duyệt.
- **Critical**: planning tính sai đáng kể, RBAC cho phép sai quyền, harvest/packing bỏ qua isolation/inspection, traceability graph sai.
- **Major**: workflow chính lỗi nhưng có workaround, validation thiếu rõ ràng, mobile upload/log dễ duplicate, report sai một phần.
- **Minor**: copy/UI gây hiểu nhầm nhẹ, sort/filter chưa tiện, empty state thiếu hướng dẫn.
- **Trivial**: lỗi hiển thị nhỏ không ảnh hưởng thao tác hoặc dữ liệu.

### QA Environments & Test Data
- **Local/dev**: dùng để RED/GREEN/REFACTOR nhanh, seed nhỏ, có thể reset thường xuyên.
- **Staging/PostgreSQL**: bắt buộc cho migration, FK/index, API contract, QR privacy, E2E smoke.
- **Demo seed canonical**: 1 admin, 1 chủ farm, 1 quản lý, 1 kỹ thuật, 1 nhân công, 1 giao hàng; 1 farm; 2 plots; 1 crop; 1 variety; đầy đủ norms.
- **Negative seed**: crop thiếu loss profile, plot đang nghỉ đất, harvest chưa nghiệm thu, chemical usage chưa hết cách ly, harvest lot đã đóng gói hết.
- **Media seed**: ảnh nhỏ/hợp lệ, ảnh quá dung lượng, file sai MIME, upload retry case.

### QC Data Quality Rules
- Các enum/state phải dùng nguồn canonical, không nhập text tự do cho trạng thái nghiệp vụ.
- Mọi quantity phải có unit; không lưu số lượng mơ hồ.
- Mọi bản ghi thực tế phải có actor/time/source context.
- Không cho orphan record ở các pivot critical: batch allocation, harvest source, packing source.
- Dữ liệu public QR phải đi qua whitelist và snapshot kiểm tra privacy.
- Thực tế không ghi đè kế hoạch; mọi adjustment phải có reason/audit.

### AI Readiness Guardrails
- **AI-readable data**: dữ liệu nghiệp vụ phải có mã định danh ổn định, trạng thái chuẩn, timestamps, actor, reason.
- **Human-in-the-loop**: AI chỉ tạo draft/recommendation cho planning, định mức, cảnh báo, SOP; mọi thay đổi chính thức cần approval.
- **Permission-aware context**: AI response phải tôn trọng role/farm scope; không lấy dữ liệu farm khác nếu user không có quyền.
- **Public/private boundary**: AI không được đưa field cấm vào QR/public output; dùng cùng whitelist với `TraceabilityPresenter`.
- **Explainability**: mọi gợi ý AI cần lưu input summary, rule/data source chính, confidence/assumption, người duyệt.
- **Feedback loop**: sau vụ, AI có thể đề xuất điều chỉnh định mức nhưng phải lưu suggestion riêng, không ghi đè chuẩn sản xuất.
- **No black-box automation for safety**: nghiệm thu, cách ly, đóng gói, giao hàng, cập nhật định mức luôn cần rule-based gate + người duyệt.

### UX Principles: Ít Thao Tác Nhất Có Thể
- **Role-first navigation**: mỗi role thấy dashboard và action chính của mình trước; không bắt nhân công/kho/giao hàng đi qua menu admin sâu.
- **Default smart, editable later**: farm hiện tại, ngày hôm nay, user hiện tại, stage hiện tại, đơn vị mặc định, plot/bed gợi ý phải được prefill khi có ngữ cảnh.
- **One primary action per screen**: mỗi màn hình vận hành chỉ có một CTA chính; action phụ nằm trong overflow/contextual toolbar.
- **Progressive disclosure**: chỉ hiện field bắt buộc và field thường dùng; optional/advanced/cost/audit mở bằng accordion hoặc tab phụ.
- **Single-column forms**: form nghiệp vụ dùng một cột để giảm đọc nhầm; nhóm field theo thứ tự thao tác thật ngoài hiện trường.
- **Persistent labels + helper text ngắn**: không dùng placeholder thay label; đơn vị đo phải nằm cạnh input.
- **Inline validation without premature blocking**: validate sau khi user rời field hoặc nhập đủ định dạng; xóa lỗi ngay khi sửa đúng; lỗi domain phải chỉ rõ field và cách sửa.
- **Bulk and quick actions**: approve nhiều task, assign nhiều task, transition nhiều task hợp lệ, import/clone định mức theo crop/variety.
- **Scan-first tables**: default sort theo việc cần xử lý trước; filter nhanh theo farm, trạng thái, ngày, role phụ trách; hiển thị badge trạng thái và cảnh báo.
- **Mobile field mode**: nhân công/kỹ thuật cần thao tác bằng 1 tay: nhận việc, chụp ảnh, ghi chú giọng nói/text ngắn, submit offline-safe.
- **No duplicate data entry**: dữ liệu đã có từ plan/batch/task phải tự kéo sang harvest/packing/delivery; user chỉ nhập thực tế khác biệt.
- **Undo/confirm by risk**: thao tác nhẹ có undo/toast; thao tác nguy hiểm như duyệt, đóng gói, giao hàng, cập nhật định mức cần confirm rõ hậu quả.

### UX Click/Tap Budgets
- Login -> thấy việc hôm nay: tối đa 1 tap sau login cho nhân công/kỹ thuật/giao hàng.
- Nhận việc -> gửi log có ảnh: tối đa 4 bước chính (mở task, nhận, chụp/đính ảnh, gửi).
- Nhập nhu cầu -> xem kế hoạch: tối đa 3 field bắt buộc ở trạng thái mặc định (crop/variety, quantity, frequency).
- Tạo lứa từ planning result: tối đa 2 bước chính nếu định mức đầy đủ (review, confirm).
- Tạo packing lot từ harvest sẵn có: tối đa 3 bước chính (chọn nguồn, xác nhận số lượng, tạo QR).
- Xem QR public: không login, dưới 2 giây phản hồi mục tiêu trên dữ liệu đã tạo.

---

## Verification Strategy (MANDATORY)

- **Infrastructure exists**: TO BE CREATED in Laravel project
- **Automated tests**: TDD
- **Framework**: PHPUnit/Pest (theo chuẩn dự án)
- **Policy**: Mỗi task phải có RED -> GREEN -> REFACTOR + QA scenario evidence.

### QA Scenario Format bắt buộc (áp dụng cho mọi task)
Mỗi scenario phải có đủ:
1) **Tool** (ví dụ: `php artisan test`, `curl`, Playwright, SQL query)
2) **Steps** (ít nhất 3 bước thao tác rõ ràng)
3) **Expected Result** (điều kiện pass/fail định lượng)
4) **Evidence Path** trong `.sisyphus/evidence/`

### TDD Evidence Convention (RED-GREEN-REFACTOR)
- RED log: `.sisyphus/evidence/task-{N}-red.log`
- GREEN log: `.sisyphus/evidence/task-{N}-green.log`
- REFACTOR regression log: `.sisyphus/evidence/task-{N}-refactor.log`
- Optional commit trace note: `.sisyphus/evidence/task-{N}-tdd-trace.md`

### Mandatory Domain Invariants
- Planning không trả field null khi đủ định mức; khi thiếu định mức phải trả domain error rõ field.
- Không tạo harvest chính thức nếu chưa đạt nghiệm thu hoặc chưa hết thời gian cách ly bắt buộc.
- Không tạo packing lot từ harvest lot không khả dụng hoặc đã đóng gói hết.
- QR public phải dựa trên dữ liệu thực tế harvest/packing, không chỉ dựa trên kế hoạch.
- QR public không được lộ tên thuốc chi tiết, liều lượng, user nội bộ, chi phí, margin.
- Mọi transition quan trọng phải có audit trail: user_id, timestamp, old_state, new_state, reason nếu cần.

---

## Execution Strategy

### Execution Waves
- **Wave 1 / MVP-0 (Foundation + Planning Blocker)**: Data contracts, core schema, auth, RBAC, master data, planning formula/service.
- **Wave 2 / MVP-1 (Production Trace Slice)**: Planting batch lifecycle, allocation, work tasks/logs, incident/isolation, inspection, harvest, packing-lot mixing, QR public.
- **Wave 3 / MVP-2 (Business Reality)**: Delivery, returns, approvals expansion, alerts, costs, price estimate, reporting baseline.
- **Wave 4 / MVP-3 (Clients Integration)**: React web + Expo flows trên API đã ổn định.
- **Final Verification Wave**: 4 review tracks song song

### Dependency Matrix (high-level)
- T1-T3 đã tạo foundation nhưng vẫn phải audit lại PostgreSQL thật.
- T4 block T5-T12; planning formula và missing-norm errors phải xanh trước khi mở rộng lifecycle/harvest/QR.
- T5-T6 block T7-T11 vì incident, inspection, harvest, packing đều cần batch/task context.
- T7-T8 block T9 vì harvest phải tôn trọng isolation/inspection guard.
- T9-T10 block T11 vì QR public phải dựa trên harvest/packing thực tế.
- T12-T14 phụ thuộc T5-T11 và được xem là MVP-2, không chặn QR MVP-1.
- T15-T16 phụ thuộc API contract ổn định từ T4-T11; không chặn backend MVP.

---

## TODOs

- [x] 1. Data Foundation Blueprint (PostgreSQL-first)

  **What to do**:
  - Chốt ERD canonical từ BRD section 28 và 12B/18 (quan hệ many-to-many critical).
  - Định nghĩa enum/state machine cho Plot, Planting Batch, Work Task, Approval.
  - Tạo migration skeleton + constraint/index strategy cho PostgreSQL.
  - Tạo Database Architecture Plan/Data Dictionary baseline trước khi thêm module downstream.
  - Audit lại migrations đã tạo bằng PostgreSQL thật; SQLite/dev evidence không đủ để đóng DB foundation.

  **QA Scenarios**:
  - Scenario: migration fresh pass
    - Tool: `php artisan migrate:fresh`
    - Steps:
      1. Reset DB và chạy toàn bộ migration.
      2. Kiểm tra exit code = 0.
      3. Verify các bảng core tồn tại (farms, plots, crops, planting_batches, etc.).
    - Expected Result: migrate thành công, exit 0, không có lỗi constraint.
    - Evidence Path: `.sisyphus/evidence/task-01-migration.log`
  - Scenario: constraint vi phạm bị chặn đúng
    - Tool: `psql` hoặc Laravel DB::statement
    - Steps:
      1. Thử insert planting_batch_plot_allocation với plot_id không tồn tại.
      2. Thử insert packing_lot_source với harvest_lot_id không tồn tại.
      3. Xác nhận DB raise lỗi FK constraint.
    - Expected Result: 2 lỗi constraint được raise đúng, không insert được.
    - Evidence Path: `.sisyphus/evidence/task-01-constraint-violation.log`
  - Scenario: database architecture QC baseline
    - Tool: PostgreSQL + SQL metadata queries
    - Steps:
      1. Chạy migrate fresh trên PostgreSQL.
      2. Kiểm tra FK/index/unique/check constraints cho bảng core.
      3. Xuất data dictionary baseline cho table/field/enum/unit/privacy classification.
    - Expected Result: constraints/indexes critical tồn tại; data dictionary có owner module và nullable/unit/status rule.
    - Evidence Path: `.sisyphus/evidence/task-01-db-architecture-qc.log`

- [x] 2. Auth + RBAC Core (Sanctum + full role v1)

  **What to do**:
  - Thiết lập Sanctum cho web + mobile token flow.
  - Ánh xạ role: Admin, Chủ farm, Quản lý farm, Kỹ thuật, Nhân công, Kho, Giao hàng.
  - Policy/Gate cho action nhạy cảm (approve, update định mức, đóng gói, giao hàng).

  **QA Scenarios**:
  - Scenario: login web/mobile thành công với role hợp lệ
    - Tool: `curl` (API) hoặc Playwright (web)
    - Steps:
      1. Gửi POST `/api/v1/auth/login` với credentials hợp lệ.
      2. Xác nhận response chứa token/session hợp lệ.
      3. Gọi GET `/api/v1/me` với token đó và verify trả về đúng role.
    - Expected Result: login 200, token valid, me response chứa role đúng.
    - Evidence Path: `.sisyphus/evidence/task-02-auth-happy.log`
  - Scenario: role không đủ quyền bị từ chối 403
    - Tool: `curl`
    - Steps:
      1. Login với role "Nhân công" (không đủ quyền duyệt).
      2. Gọi POST `/api/v1/approvals/{id}/approve` với token đó.
      3. Xác nhận response trả 403 Forbidden.
    - Expected Result: HTTP 403 với body chứa error message phù hợp.
    - Evidence Path: `.sisyphus/evidence/task-02-auth-403.log`

- [x] 3. Master Data Modules (Farm/Plot/Bed/Crop/Variety/Norms)

  **What to do**:
  - CRUD Filament + API read models cho farm, lô, luống, cây, giống, chuẩn, định mức.
  - Rule validation: dữ liệu bắt buộc cho tính kế hoạch.

  **QA Scenarios**:
  - Scenario: tạo crop variety gắn đúng profile/định mức
    - Tool: `curl` hoặc Filament form submit
    - Steps:
      1. Tạo Crop Variety mới gắn với Crop cha và Production Profile.
      2. Verify response trả về đầy đủ ID và các liên kết.
      3. GET lại resource để xác nhận data đúng.
    - Expected Result: 201 Created, data khớp input, relationship loaded.
    - Evidence Path: `.sisyphus/evidence/task-03-master-happy.log`
  - Scenario: tạo lô với diện tích âm hoặc vượt farm bị chặn
    - Tool: `curl` POST
    - Steps:
      1. POST tạo Plot với area = -100 (âm).
      2. POST tạo Plot với total_area > farm_area.
      3. Xác nhận cả 2 request đều trả 422 Validation Error.
    - Expected Result: HTTP 422, response chứa field "area" error message.
    - Evidence Path: `.sisyphus/evidence/task-03-master-validation.log`

- [x] 4. Demand & Contract Input + Planning API v1 (MVP-0 BLOCKER)

  **What to do**:
  - Tạo contract/demand entity theo đơn vị/tần suất.
  - Implement planning calculation service m2 -> cây -> sản lượng -> hao hụt -> thành phẩm.
  - Version hóa API `/api/v1/planning/...`.
  - Viết Planning Formula Spec trước khi mở rộng batch/harvest/QR.
  - Controller chỉ validate/request-response; công thức và invariant phải nằm trong `PlanningCalculationService`.
  - UX planning form mặc định chỉ yêu cầu 3 field: crop/variety, quantity, frequency; các giả định khác lấy từ định mức và cho phép mở rộng/chỉnh sau.
  - Planning input/output phải có assumptions cho season/crop cycle/climate_zone, unit conversion, rounding, customer/channel nếu có.
  - Response phải trả fulfillment status: đủ/thiếu đất, thiếu cây, thiếu nhân công, rủi ro ngày giao, shortage warnings.

  **Exit Gate**:
  - Không bắt đầu T5-T12 nếu T4 chưa có test xanh cho happy path, missing norm, unit/frequency conversion, rounding.
  - Evidence bắt buộc đã tạo: `.sisyphus/evidence/task-04-planning-formula-spec.md`.
  - Gaps còn phải đóng trước downstream rộng: assumptions object, fulfillment status, execution-safe rounding, unit conversion profile, plan snapshot.

  **QA Scenarios**:
  - Scenario: demand 10kg/ngày trả về đủ output planning fields
    - Tool: `curl POST /api/v1/planning/calculate`
    - Steps:
      1. POST với payload: `{quantity: 10, unit: "kg", frequency: "daily", crop_id: <id>}`.
      2. Verify response chứa tất cả field: sản lượng thu thô, số cây, m2, ngày bắt đầu.
      3. Verify không có field nào bị null khi định mức đầy đủ.
    - Expected Result: 200, response chứa ≥8 planning output fields, không error.
    - Evidence Path: `.sisyphus/evidence/task-04-planning-happy.log`
  - Scenario: thiếu định mức bắt buộc trả domain error rõ ràng
    - Tool: `curl POST /api/v1/planning/calculate`
    - Steps:
      1. POST với crop_id không có loss_profile hoặc planting_density.
      2. Xác nhận lỗi trả về rõ domain message (không phải generic 500).
      3. Kiểm tra error body chỉ rõ field missing là gì.
    - Expected Result: 422 hoặc 400, body có "field" + "message" cụ thể.
    - Evidence Path: `.sisyphus/evidence/task-04-planning-domain-error.log`

- [x] 5. Planting Batch Lifecycle + Allocation Engine (MVP-1)

  **What to do**:
  - Implement lifecycle 14 trạng thái lứa trồng theo BRD 12B.2.
  - Allocation many-to-many lứa ↔ lô/luống, có kiểm tra đất sẵn sàng/luân canh.
  - Domain rules nằm trong `PlantingBatchLifecycleService` và `AllocationService`; Filament/API chỉ gọi service.
  - Allocation phải xét soil history/crop family/rest period/incidents để cảnh báo hoặc chặn theo rule cấu hình.
  - Batch lifecycle phải hỗ trợ một lứa có nhiều harvest lots theo nhiều ngày/đợt.

  **Implementation status (2026-05-13)**:
  - ✅ Schema/model foundation, lifecycle API/service, and allocation guard service are implemented.
  - ✅ Evidence: `.sisyphus/evidence/task-05-batch-foundation.md`, `.sisyphus/evidence/task-05-lifecycle-api.md`, `.sisyphus/evidence/task-05-allocation-guards.md`.
  - 🟡 Still pending before closing T5 fully: audit/event hooks for lifecycle transitions and allocation changes.

  **QA Scenarios**:
  - Scenario: chuyển trạng thái đúng trình tự có audit log
    - Tool: `curl POST /api/v1/planting-batches/{id}/transition`
    - Steps:
      1. Chuyển batch từ "chờ duyệt" → "đang làm đất" (valid transition).
      2. Verify response 200 và batch status đã cập nhật.
      3. GET audit trail và xác nhận log chứa user_id + timestamp + trạng thái cũ/mới.
    - Expected Result: 200, status updated, audit log đầy đủ user + time.
    - Evidence Path: `.sisyphus/evidence/task-05-batch-transition-happy.log`
  - Scenario: phân bổ vào lô đang nghỉ bị cảnh báo/chặn
    - Tool: `curl POST /api/v1/planting-batches/{id}/allocate`
    - Steps:
      1. POST allocation với plot đang ở trạng thái "đang nghỉ đất".
      2. Xác nhận hệ thống trả cảnh báo hoặc reject.
      3. Thử allocation với plot "tạm ngưng sử dụng", phải reject.
    - Expected Result: Cảnh báo hoặc 422 với message rõ plot status.
    - Evidence Path: `.sisyphus/evidence/task-05-allocation-blocked.log`

- [x] 6. Work Task & Farming Log (MVP-1 API-first)

  **What to do**:
  - Sinh task từ stage template, giao việc theo lứa/farm/lô.
  - API cho Expo: nhận việc, báo cáo thực tế, ảnh, ghi chú, thời gian.
  - Tách rõ kế hoạch vs thực tế.
  - Chỉ cần API/mobile contract ở MVP-1; Expo UI triển khai ở T16 sau khi contract ổn định.
  - API response phải đủ dữ liệu để UI hiển thị "việc hôm nay" không cần gọi thêm nhiều endpoint.
  - Log thực tế phải prefill batch/task/user/time; nhân công chỉ nhập khác biệt thực tế và ảnh/ghi chú nếu cần.

  **Implementation status (2026-05-13)**:
  - ✅ Work task schema/model and batch/allocation relationships are implemented.
  - ✅ Work task generation from growth stages is implemented with allocation-specific idempotency and cross-batch allocation guard.
  - ✅ Work task list/show/generate/status API is implemented with farm scope, assignment guard, and terminal transition tests.
  - ✅ Farming log schema/model foundation is implemented.
  - ✅ Farming log submission API is implemented with context prefill, path-photo validation, multipart photo upload, public disk storage, and task completion update.
  - ✅ Evidence: `.sisyphus/evidence/task-06-work-task-schema.md`, `.sisyphus/evidence/task-06-task-generation-service.md`, `.sisyphus/evidence/task-06-work-task-api.md`, `.sisyphus/evidence/task-06-farming-log-foundation.md`, `.sisyphus/evidence/task-06-work-task-log-api.md`.
  - 🟡 Still pending before closing T6 fully: manual/mobile smoke evidence for receive task → submit log → photo stored.

  **QA Scenarios**:
  - Scenario: nhân công nhận task và gửi log có ảnh thành công
    - Tool: `curl POST /api/v1/work-tasks/{id}/logs` + `curl -F file=@...`
    - Steps:
      1. Nhận task (PATCH status → accepted).
      2. POST farming log kèm ảnh và ghi chú thực tế.
      3. Verify log được tạo, ảnh đã upload, status cập nhật.
    - Expected Result: 201 Created, log ID trả về, ảnh accessible.
    - Evidence Path: `.sisyphus/evidence/task-06-task-log-happy.log`
  - Scenario: log thiếu ảnh bắt buộc bị từ chối
    - Tool: `curl POST /api/v1/work-tasks/{id}/logs`
    - Steps:
      1. POST log với required photo = true nhưng gửi không kèm file.
      2. Xác nhận response 422.
      3. Thử POST log với payload thiếu trường bắt buộc khác.
    - Expected Result: HTTP 422, response chỉ rõ field nào bị thiếu.
    - Evidence Path: `.sisyphus/evidence/task-06-task-log-validation.log`

- [x] 7. Incident + Chemical/Biological Usage + Isolation Guard

  **What to do**:
  - Ghi nhận sâu bệnh/phát sinh + xử lý + thuốc/sinh học.
  - Tính và cảnh báo thời gian cách ly trước thu hoạch.
  - Ghi usage/cost snapshot cho thuốc/sinh học liên quan incident mà không cần bật full inventory ledger.

  **Implementation status (2026-05-13)**:
  - ✅ Incident schema/model/API is implemented with farm-scope checks.
  - ✅ Chemical/biological usage schema/model/API is implemented with dosage, quantity, cost snapshot, applied user, and isolation days.
  - ✅ Isolation end date calculation and `IsolationGuardService` are implemented.
  - ✅ Evidence: `.sisyphus/evidence/task-07-incident-chemical-isolation.md`.
  - 🟡 Still pending before closing T7 fully: manual/API smoke logs.

  **QA Scenarios**:
  - Scenario: ghi nhận incident + chemical usage đầy đủ trace
    - Tool: `curl POST /api/v1/incidents` + `curl POST /api/v1/chemical-usages`
    - Steps:
      1. Tạo incident record cho sâu bệnh.
      2. Tạo chemical usage gắn incident, ghi đầy đủ thuốc + liều lượng + người xử lý.
      3. Xác nhận chemical_usage_id gắn đúng với incident.
    - Expected Result: 201, incident và usage liên kết đúng, không orphan record.
    - Evidence Path: `.sisyphus/evidence/task-07-chemical-trace.log`
  - Scenario: tạo harvest khi chưa hết cách ly bị fail
    - Tool: `curl POST /api/v1/harvest-lots`
    - Steps:
      1. Ghi nhận chemical usage với isolation_days = 7.
      2. Thử tạo harvest lot sau 3 ngày (chưa đủ 7).
      3. Xác nhận bị reject với message "chưa đủ thời gian cách ly".
    - Expected Result: HTTP 422, body có message cụ thể về cách ly.
    - Evidence Path: `.sisyphus/evidence/task-07-isolation-block.log`

- [x] 8. Pre-harvest Inspection + Approval Gate

  **What to do**:
  - Checklist nghiệm thu trước thu hoạch theo section 15.
  - Approval workflow: request -> approve/reject -> reason/audit trail.
  - Override harvest eligibility nếu có phải theo Override Governance Matrix: role, condition, reason, audit, review.

  **Implementation status (2026-05-13)**:
  - ✅ Pre-harvest inspection schema/model/API is implemented.
  - ✅ Approval workflow supports submitted/approved/rejected, approver role enforcement, and fail criteria blocking.
  - ✅ `HarvestEligibilityService` requires latest inspection to be approved and also checks chemical isolation via `IsolationGuardService`.
  - ✅ Evidence: `.sisyphus/evidence/task-08-pre-harvest-inspection.md`.
  - 🟡 Still pending before closing T8 fully: manual/API smoke logs.

  **QA Scenarios**:
  - Scenario: inspection đạt chuẩn mở quyền tạo harvest
    - Tool: `curl POST /api/v1/pre-harvest-inspections`
    - Steps:
      1. POST inspection với checklist đầy đủ, tất cả criteria = pass.
      2. Verify inspection status = "approved".
      3. Thử POST harvest lot cho lứa đó — phải được phép.
    - Expected Result: inspection 201/200, harvest creation 201.
    - Evidence Path: `.sisyphus/evidence/task-08-inspection-pass.log`
  - Scenario: inspection fail chặn harvest lot creation
    - Tool: `curl POST /api/v1/pre-harvest-inspections`
    - Steps:
      1. POST inspection với ít nhất 1 criteria fail.
      2. Thử POST harvest lot cho lứa đó ngay sau fail.
      3. Xác nhận bị chặn hoặc cảnh báo rõ ràng.
    - Expected Result: HTTP 422 hoặc 403, message chỉ inspection fail.
    - Evidence Path: `.sisyphus/evidence/task-08-inspection-fail.log`

- [x] 9. Harvest Module + Grade Breakdown

  **What to do**:
  - Phiếu thu hoạch: sản lượng thô + A/B/C/loại bỏ + lý do lỗi.
  - Liên kết chặt với lứa trồng và thời điểm thu.
  - Form thu hoạch kéo sẵn batch/crop/plot/task/inspection context; người dùng chỉ nhập số lượng thực tế và grade breakdown.
  - Một planting batch có thể tạo nhiều harvest lots; batch actual yield là tổng các harvest lots.
  - Grade/reject phải map với product standard và reject reason canonical.

  **Implementation status (2026-05-13)**:
  - ✅ Harvest lot schema/model/API is implemented.
  - ✅ `HarvestLotService` wires `HarvestEligibilityService` into harvest creation, so inspection and isolation gates are enforced.
  - ✅ Grade A/B/C/reject validation rejects totals greater than raw quantity.
  - ✅ Planting batch `actual_quantity`, `actual_harvest_date`, and `status=harvesting` are updated from harvest lots.
  - ✅ Evidence: `.sisyphus/evidence/task-09-harvest-lots.md`.
  - 🟡 Still pending before closing T9 fully: normalize product standard/reject reason taxonomy and capture manual/API smoke logs.

  **QA Scenarios**:
  - Scenario: thu hoạch hợp lệ ghi nhận đủ grade breakdown
    - Tool: `curl POST /api/v1/harvest-lots`
    - Steps:
      1. POST harvest với đầy đủ: raw_qty, grade_a, grade_b, grade_c, reject, reasons.
      2. Verify response trả về tất cả field đã nhập.
      3. Verify grade breakdown tổng khớp raw_qty sau hao hụt.
    - Expected Result: 201, data lưu đúng, grade sum consistent.
    - Evidence Path: `.sisyphus/evidence/task-09-harvest-happy.log`
  - Scenario: tổng grade vượt sản lượng thô bị reject
    - Tool: `curl POST /api/v1/harvest-lots`
    - Steps:
      1. POST harvest với grade_a + b + c + reject > raw_qty (invalid).
      2. Xác nhận bị validation reject.
      3. Thử với raw_qty = 0 hoặc null.
    - Expected Result: HTTP 422, message chỉ tổng vượt raw_qty.
    - Evidence Path: `.sisyphus/evidence/task-09-harvest-validation.log`

- [x] 10. Processing + Packing Lot Mixing + Traceability Graph (MVP-1)

  **What to do**:
  - Sơ chế record trước/sau, tỷ lệ hao hụt.
  - Packing lot many-to-many nhiều nguồn harvest.
  - Build traceability graph backend cho QR public.
  - Không tạo QR từ kế hoạch; trace graph phải lấy dữ liệu harvest/processing/packing thực tế.
  - Packing UI/API phải hỗ trợ chọn nhiều harvest lot bằng filter và bulk selection; quantity còn khả dụng phải hiển thị ngay.

  **QA Scenarios**:
  - Scenario: packing lot trộn 2 nguồn farm hiển thị đúng nguồn
    - Tool: `curl POST /api/v1/packing-lots` + `curl GET /api/v1/packing-lots/{id}`
    - Steps:
      1. Tạo packing lot với 2 nguồn harvest từ farm A và farm B.
      2. GET packing lot và verify sources chứa cả 2 farm.
      3. Verify QR generation chứa đầy đủ source info.
    - Expected Result: 201, GET response có sources array ≥2 farm.
    - Evidence Path: `.sisyphus/evidence/task-10-packing-mix.log`
  - Scenario: nguồn harvest không hợp lệ bị chặn
    - Tool: `curl POST /api/v1/packing-lots`
    - Steps:
      1. POST packing lot gắn harvest_lot_id đã ở trạng thái "đã đóng gói" (không khả dụng).
      2. Xác nhận bị reject.
      3. Thử gắn harvest từ batch khác trạng thái.
    - Expected Result: HTTP 422, message chỉ harvest không khả dụng.
    - Evidence Path: `.sisyphus/evidence/task-10-packing-source-invalid.log`

- [x] 11. Public QR Traceability Page (MVP-1 Exit)

  **What to do**:
  - Endpoint/public page không login, responsive.
  - Hiển thị đúng field công khai, ẩn field nhạy cảm.
  - Đây là checkpoint kết thúc MVP-1: demand -> QR phải chạy được một happy path tự động.
  - QR public page ưu tiên đọc nhanh trên điện thoại: crop, farm/source, ngày thu hoạch/đóng gói, trạng thái an toàn, chứng nhận; chi tiết nguồn mở theo accordion.

  **Implementation status (2026-05-13)**:
  - ✅ Public JSON endpoint `/api/v1/traceability/{qrCode}` is implemented without auth.
  - ✅ Public responsive page `/traceability/{qrCode}` is implemented with consumer-safe fields.
  - ✅ Public presenter uses an explicit whitelist and hides chemical details, dosage, internal users, cost, and margin.
  - ✅ Evidence: `.sisyphus/evidence/task-11-green.log`, `.sisyphus/evidence/task-11-refactor.log`, `.sisyphus/evidence/task-11-migration.log`, `.sisyphus/evidence/task-11-routes.log`, `.sisyphus/evidence/task-11-lint.log`.

  **QA Scenarios**:
  - Scenario: quét QR xem đủ thông tin truy xuất cho consumer
    - Tool: `curl GET /api/v1/traceability/{qr_code}` (không auth)
    - Steps:
      1. Tạo packing lot và lấy QR code.
      2. GET endpoint public không auth với QR code.
      3. Verify response chứa farm, crop, ngày trồng, ngày thu, certification.
    - Expected Result: 200, response có ≥6 public fields (không có thuốc chi tiết, chi phí).
    - Evidence Path: `.sisyphus/evidence/task-11-qr-public.log`
  - Scenario: public QR không lộ thông tin cấm
    - Tool: `curl` + read response body
    - Steps:
      1. GET QR public endpoint.
      2. Verify response không chứa: tên thuốc chi tiết, liều lượng, user nội bộ, chi phí.
      3. Verify status "tuân thủ kiểm soát an toàn" hiển thị nếu có nhật ký.
    - Expected Result: Không field cấm, status safety hiển thị đúng.
    - Evidence Path: `.sisyphus/evidence/task-11-qr-privacy.log`

- [x] 12. Delivery + Return + Revenue Reality (MVP-2)

  **What to do**:
  - Delivery note + acceptance record + return record.
  - Tính doanh thu thực tế: delivered - returned + side-channel.
  - Contract/customer baseline phải lưu customer/channel, delivery cadence, tolerance, price snapshot hoặc pricing reference.
  - Return reason phải map về packing lot/source harvest/batch nếu có thể để phục vụ báo cáo chất lượng và cải tiến định mức.

  **Implementation status (2026-05-13)**:
  - ✅ Delivery note, acceptance record, and return record schema/models are implemented.
  - ✅ Delivery API stores customer/contract and unit-price snapshots and exposes `/api/v1/deliveries/{id}/revenue`.
  - ✅ Return API updates returned quantity, net quantity, return deduction, side-channel revenue, and net revenue.
  - ✅ Return records link back to delivery note and packing lot for quality/traceability feedback.
  - ✅ Evidence: `.sisyphus/evidence/task-12-green.log`, `.sisyphus/evidence/task-12-refactor.log`, `.sisyphus/evidence/task-12-migration.log`, `.sisyphus/evidence/task-12-routes.log`, `.sisyphus/evidence/task-12-lint.log`.

  **QA Scenarios**:
  - Scenario: giao hàng hoàn tất cập nhật doanh thu đúng
    - Tool: `curl POST /api/v1/deliveries` + `GET /api/v1/deliveries/{id}/revenue`
    - Steps:
      1. Tạo delivery note với số lượng khách nhận = 100.
      2. Xác nhận revenue calculation được cập nhật = qty × price.
      3. Verify revenue snapshot lưu đúng giá tại thời điểm giao.
    - Expected Result: revenue = 100 × price tại delivery date, snapshot stored.
    - Evidence Path: `.sisyphus/evidence/task-12-delivery-revenue.log`
  - Scenario: trả hàng cập nhật báo cáo hao hụt đúng
    - Tool: `curl POST /api/v1/returns`
    - Steps:
      1. Tạo delivery trước đó.
      2. POST return với số lượng trả = 5 và lý do cụ thể.
      3. Verify delivery + return affect đúng hao hụt và lý do.
    - Expected Result: delivery net delivered updated, return report row created.
    - Evidence Path: `.sisyphus/evidence/task-12-return-flow.log`

- [x] 13. Costing + Price Table + Margin Dashboard v1 (MVP-2)

  **What to do**:
  - Cost records theo lứa: giống, phân, thuốc, nước, nhân công...
  - Price table theo crop/variety/time.
  - Báo cáo lợi nhuận dự kiến vs thực tế.

  **Implementation status (2026-05-13)**:
  - ✅ Price table schema/model/API is implemented for crop/variety/date/unit prices.
  - ✅ Cost record schema/model/API is implemented with canonical cost categories.
  - ✅ Production plan creation uses active price table to calculate estimated revenue, margin, margin percent, and pricing snapshot.
  - ✅ Margin dashboard API summarizes estimated revenue/cost/margin and actual revenue/cost/margin.
  - ✅ Evidence: `.sisyphus/evidence/task-13-green.log`, `.sisyphus/evidence/task-13-refactor.log`, `.sisyphus/evidence/task-13-migration.log`, `.sisyphus/evidence/task-13-routes.log`, `.sisyphus/evidence/task-13-lint.log`.

  **QA Scenarios**:
  - Scenario: tạo kế hoạch có estimate margin đầy đủ
    - Tool: `curl POST /api/v1/production-plans`
    - Steps:
      1. Tạo production plan với crop + quantity + price table active.
      2. Verify response chứa: estimated_cost, estimated_revenue, margin, margin_percent.
      3. Verify margin tính đúng = revenue - cost.
    - Expected Result: 201, margin = revenue - cost (số dương hoặc âm đúng).
    - Evidence Path: `.sisyphus/evidence/task-13-margin-happy.log`
  - Scenario: chi phí thiếu phân loại bắt buộc bị validation fail
    - Tool: `curl POST /api/v1/cost-records`
    - Steps:
      1. POST cost record không có cost_category (bắt buộc).
      2. Xác nhận bị 422 với error chỉ rõ field "cost_category".
      3. Thử POST cost với category không nằm trong whitelist enum.
    - Expected Result: HTTP 422, message chỉ cost_category.
    - Evidence Path: `.sisyphus/evidence/task-13-cost-validation.log`

- [x] 14. Alerts + Notifications Contract (web + Expo) (MVP-2)

  **What to do**:
  - Alert rules theo BRD section 24.
  - Notification contract cho web/Expo (in-app + push-ready payload).

  **Implementation status (2026-05-13)**:
  - ✅ Alert schema/model/API is implemented with farm scope, recipient role/user, status, context, and push-ready notification payload.
  - ✅ Manual trigger endpoint `/api/v1/alerts/trigger` runs overdue work-task and yield-shortfall rules; same service can be scheduled later.
  - ✅ Alerts list and mark-read endpoints are implemented.
  - ✅ Evidence: `.sisyphus/evidence/task-14-green.log`, `.sisyphus/evidence/task-14-refactor.log`, `.sisyphus/evidence/task-14-migration.log`, `.sisyphus/evidence/task-14-routes.log`, `.sisyphus/evidence/task-14-lint.log`.

  **QA Scenarios**:
  - Scenario: task trễ hạn sinh alert đúng role nhận
    - Tool: `curl POST /api/v1/work-tasks/{id}/delay` + `GET /api/v1/alerts`
    - Steps:
      1. Tạo task có due_date hôm nay, để status = "chưa làm".
      2. Chạy alert trigger (scheduled job hoặc manual).
      3. GET alerts và verify có alert mới với recipient = "Quản lý farm" (role phù hợp).
    - Expected Result: alert tạo đúng, recipient role khớp.
    - Evidence Path: `.sisyphus/evidence/task-14-alert-happy.log`
  - Scenario: thiếu sản lượng so kế hoạch sinh cảnh báo ngữ cảnh
    - Tool: `curl GET /api/v1/alerts`
    - Steps:
      1. Tạo lứa trồng với sản lượng dự kiến 100kg, thực tế chỉ 50kg sau harvest.
      2. Trigger alert check (hoặc chạy job).
      3. Verify alert chỉ rõ: crop, lứa, thiếu 50kg so với kế hoạch.
    - Expected Result: alert chứa context đầy đủ (crop_id, batch_id, gap qty).
    - Evidence Path: `.sisyphus/evidence/task-14-yield-alert.log`

- [x] 15. React Web Operations Surface (MVP-3)

  **What to do**:
  - React web cho các màn hình vận hành cần ngoài Filament (nếu chọn): dashboard/timeline/task monitor.
  - Dùng cùng API contract v1.
  - Không build landing page; first screen là operations dashboard theo role với action cần làm ngay.
  - Mọi màn hình table phải có search/filter chính, default sort theo mức khẩn cấp/ngày, contextual bulk action.

  **Implementation status (2026-05-13)**:
  - ✅ React operations SPA is available at `/operations` and `/operations/login`, backed by Laravel route fallback `/operations/{any?}`.
  - ✅ Login/logout/token persistence uses API v1 auth contract; API client redirects to login on 401 and sends Bearer token for operations data calls.
  - ✅ Dashboard loads alerts, planting batches, and work tasks from API v1 with search, tab filters, urgency/date/status sorting, and resilient Eloquent JSON field mapping.
  - ✅ Vite config keeps the legacy Laravel welcome asset entry while adding the React operations entry.
  - ✅ Evidence: `.sisyphus/evidence/task-15-build.log`, `.sisyphus/evidence/task-15-green.log`, `.sisyphus/evidence/task-15-refactor.log`, `.sisyphus/evidence/task-15-routes.log`, `.sisyphus/evidence/task-15-lint.log`.

  **QA Scenarios**:
  - Scenario: React web hiển thị đúng trạng thái lứa + task realtime
    - Tool: Playwright
    - Steps:
      1. Mở React web dashboard với token hợp lệ.
      2. Navigate đến màn hình lứa trồng, xác nhận hiển thị đúng status.
      3. Tạo task mới từ Filament, refresh React web — verify hiển thị mới.
    - Expected Result: Trạng thái batch đúng, task mới xuất hiện sau refresh.
    - Evidence Path: `.sisyphus/evidence/task-15-react-happy.log`
  - Scenario: token hết hạn xử lý refresh/re-login đúng
    - Tool: Playwright
    - Steps:
      1. Để token hết hạn (sau expiry time).
      2. Thử thao tác tiếp (tạo/cập nhật).
      3. Xác nhận app redirect về login hoặc hiện popup refresh.
    - Expected Result: User được redirect hoặc prompted, không silent fail.
    - Evidence Path: `.sisyphus/evidence/task-15-token-expiry.log`

- [x] 16. Expo App Field Workflow (MVP-3)

  **What to do**:
  - Luồng mobile: nhận task -> ghi log -> chụp ảnh -> sync trạng thái.
  - Chuẩn bị nền cho offline queue (phase-safe), tối thiểu sync retry.
  - Mobile first screen là "Việc hôm nay"; nhận việc và gửi log nằm trong một flow ngắn, thao tác được bằng một tay.
  - Không yêu cầu nhập lại dữ liệu đã biết từ task; hỗ trợ ảnh, ghi chú ngắn, retry upload, và trạng thái sync rõ ràng.

  **Implementation status (2026-05-13)**:
  - ✅ Self-contained Expo app lives under `mobile/field-app`; dependencies are isolated from the Laravel/Vite root.
  - ✅ Auth service follows API v1 response wrapping and persists token/user session locally.
  - ✅ "Việc hôm nay" loads assigned due tasks, supports accept/start actions, camera photo capture, log submission, and sync state display.
  - ✅ Farming log submission uses `/api/v1/work-tasks/{id}/logs`; successful log sync relies on backend completion instead of a duplicate mobile status update.
  - ✅ Offline queue persists pending logs, prevents duplicate local IDs, marks synced/failed states, and supports manual retry.
  - ✅ Evidence: `.sisyphus/evidence/task-16-expo-sync-happy.log`, `.sisyphus/evidence/task-16-expo-retry.log`, `.sisyphus/evidence/task-16-mobile-build.log`, `.sisyphus/evidence/task-16-refactor.log`, `.sisyphus/evidence/task-16-green.log`.

  **QA Scenarios**:
  - Scenario: Expo nhận task, ghi log, sync thành công
    - Tool: Expo app + curl API (backend side)
    - Steps:
      1. Expo: login → fetch task list → accept task.
      2. Expo: submit log kèm ảnh + ghi chú.
      3. Backend: GET task log xác nhận log đã sync, ảnh accessible.
    - Expected Result: 201, log trên server khớp input, ảnh upload OK.
    - Evidence Path: `.sisyphus/evidence/task-16-expo-sync-happy.log`
  - Scenario: upload ảnh lỗi mạng retry không mất log
    - Tool: Expo app (network mocking)
    - Steps:
      1. Expo: tạo log kèm ảnh.
      2. Simulate network fail ở upload ảnh, verify log vẫn queued.
      3. Restore network, verify retry thành công và log không bị duplicate.
    - Expected Result: Log vẫn tồn tại sau retry, ảnh cuối cùng có trên server.
    - Evidence Path: `.sisyphus/evidence/task-16-expo-retry.log`

- [x] 17. TDD Quality Net + Test Data Factory (MVP Hardening)

  **What to do**:
  - Tạo test factories/seed fixtures cho domain nông nghiệp.
  - Thiết lập test pyramid: unit (formula/state), feature/API (workflow), smoke E2E.
  - Bắt buộc xuất evidence RED/GREEN/REFACTOR theo convention trong Verification Strategy.

  **Implementation status (2026-05-13)**:
  - ✅ 20 domain model factories created: Farm, Crop, CropVariety, Plot, Bed, ProductionPlan, PlantingBatch, PlantingBatchAllocation, GrowthStage, WorkTask, FarmingLog, HarvestLot, ProcessingRecord, PackingLot, DeliveryNote, ReturnRecord, Alert, CostRecord, PriceTable, SupplyDemand.
  - ✅ Factories include named states for key statuses (cancelled, done, active, published, etc.) and relationship factories.
  - ✅ `HasFactory` trait added to all domain models.
  - ✅ `SimpleFactoryTest` verifies the factory RED→GREEN path with a persisted farm.
  - ✅ `FactoryRegressionTest::test_it_can_create_a_complete_farm_workflow_using_factories` builds a full farm workflow fixture covering all 20 new factories.
  - ✅ `FactoryRegressionTest::test_cancelled_work_task_cannot_accept_logs` exercises the API rule and passes twice cleanly.
  - ✅ Full suite: 281 tests, 963 assertions, all pass after audit/offline hardening.
  - ✅ Evidence: `.sisyphus/evidence/task-17-red.log`, `.sisyphus/evidence/task-17-green.log`, `.sisyphus/evidence/task-17-refactor.log`, `.sisyphus/evidence/task-17-known-rule.log`.
  - ✅ Fixed PHPUnit 12 method naming (`@test` → `test_` prefix) in FactoryRegressionTest.

  **QA Scenarios**:
  - Scenario: RED-GREEN-REFACTOR trace
    - Tool: `php artisan test` + evidence logs
    - Steps:
      1. Chạy test mới ở trạng thái RED, lưu log vào `.sisyphus/evidence/task-17-red.log`.
      2. Implement tối thiểu để GREEN, chạy lại test và lưu `.sisyphus/evidence/task-17-green.log`.
      3. Refactor an toàn, chạy full suite liên quan và lưu `.sisyphus/evidence/task-17-refactor.log`.
    - Expected Result: 3 log tồn tại, RED có failing assertion, GREEN/REFACTOR đều pass.
  - Scenario: regression known-rule
    - Tool: `php artisan test --filter=...`
    - Steps:
      1. Chạy test case rule business đã từng lỗi.
      2. Xác nhận pass ở branch hiện tại.
      3. Ghi kết quả vào evidence note.
    - Expected Result: regression test pass ổn định 2 lần chạy liên tiếp.

- [x] 18. Wave Integration & UAT Readiness Pack (MVP Hardening)

  **What to do**:
  - Gom checklist UAT theo vai trò full v1.
  - Chuẩn bị evidence artifacts cho toàn chuỗi nghiệp vụ.

  **Implementation status (2026-05-13)**:
  - ✅ Dry-run chain runner created at `.sisyphus/run-continuation/task-18-e2e-chain.sh`.
  - ✅ E2E chain evidence captured at `.sisyphus/evidence/task-18-e2e-chain.log`, covering planning, planting batch, allocation guards, work task/log, inspection, harvest, packing/QR/traceability, delivery, return, and revenue slices.
  - ✅ UAT readiness pack created at `.sisyphus/evidence/task-18-uat-readiness.md`.
  - ✅ Fail-path runner created at `.sisyphus/run-continuation/task-18-fail-paths.sh`.
  - ✅ Fail-path evidence captured at `.sisyphus/evidence/task-18-fail-paths.log`.
  - ✅ Final regression evidence captured at `.sisyphus/evidence/task-18-refactor.log`.
  - ✅ Verification: fail-path runner passes 11 tests / 41 assertions plus full suite; full suite passes 281 tests / 963 assertions after audit/offline hardening.
  - ⚠️ Intentional gap: dry-run chain uses focused API workflow tests instead of one mutable curl chain because allocation currently has no public API endpoint.

  **QA Scenarios**:
  - Scenario: dry-run end-to-end demand→delivery pass
    - Tool: Bash script (curl chain) hoặc Postman collection
    - Steps:
      1. Tạo demand (10kg/ngày) → sinh production plan.
      2. Tạo planting batch → allocate plots → work tasks → farming log.
      3. Inspection → harvest → processing → packing lot → QR → delivery.
    - Expected Result: Toàn chain tạo thành công, không lỗi 500/validation.
    - Evidence Path: `.sisyphus/evidence/task-18-e2e-chain.log`
  - Scenario: fail path inspection fail / isolation fail / return pass đúng kỳ vọng
    - Tool: Bash script (curl chain)
    - Steps:
      1. Chạy inspection fail → verify harvest bị chặn.
      2. Chạy isolation fail → verify harvest bị chặn.
      3. Chạy delivery → return → verify return report updated.
    - Expected Result: Fail paths fail đúng, return path pass, tất cả logged.
    - Evidence Path: `.sisyphus/evidence/task-18-fail-paths.log`

---

## Final Verification Wave

- [x] F1. Plan Compliance Audit (oracle)
  - Tool: oracle + grep/read checklist
  - Steps:
    1. Đối chiếu từng Must Have/Must NOT Have với artifact và code paths.
    2. Xác nhận evidence files tồn tại cho task 1-18.
    3. Lập bảng pass/fail theo từng mục.
  - Expected Result: 100% Must Have pass, 0 Must NOT Have violation.
  - Evidence Path: `.sisyphus/evidence/final-f1-plan-compliance.md`

- [x] F1b. QA Traceability Matrix
  - Tool: BRD checklist + test/evidence index
  - Steps:
    1. Map từng requirement chính từ BRD sang task tương ứng.
    2. Map từng task sang test case và evidence file.
    3. Đánh dấu requirement thiếu test/evidence là release blocker nếu thuộc MVP-0/MVP-1.
  - Expected Result: 100% MVP-0/MVP-1 requirement có test + evidence; không có orphan requirement critical.
  - Evidence Path: `.sisyphus/evidence/final-f1b-qa-traceability.md`

- [x] F2. Code Quality Review (unspecified-high)
  - Tool: `php artisan test`, `phpstan`/lint, static scan
  - Steps:
    1. Chạy full test suite.
    2. Chạy static analysis/lint theo chuẩn dự án.
    3. Tổng hợp lỗi blocker và xác nhận clean run.
  - Expected Result: test pass, static checks pass, không còn blocker.
  - Evidence Path: `.sisyphus/evidence/final-f2-quality.log`

- [x] F2b. Data Quality + Migration QC
  - Tool: PostgreSQL migration fresh + SQL integrity checks
  - Steps:
    1. Chạy migrate fresh + seed canonical trên PostgreSQL staging.
    2. Chạy integrity checks cho FK/pivot critical, enum/state, quantity unit, orphan records.
    3. Chạy negative seed để xác nhận constraint/domain rule chặn đúng.
  - Expected Result: Không orphan record, không enum lạ, không quantity thiếu unit, negative cases fail đúng.
  - Evidence Path: `.sisyphus/evidence/final-f2b-data-quality.log`

- [x] F2c. Database Architecture Review
  - Tool: PostgreSQL catalog queries + EXPLAIN on critical API queries
  - Steps:
    1. Review schema modules, FK, unique/check constraints, indexes, and nullable columns against Data Dictionary.
    2. Run EXPLAIN for critical queries: planning lookup, task list by role/farm/status/date, harvest eligibility, packing source graph, QR traceability.
    3. Verify audit/event tables capture sensitive actions and no traceability-critical records depend on soft delete only.
  - Expected Result: No missing critical FK/index/unique/check constraint; critical queries have reasonable indexed plans; audit/event coverage complete for MVP-1.
  - Evidence Path: `.sisyphus/evidence/final-f2c-db-architecture.md`

- [x] F3. Real Manual QA + Playwright/UI + API evidence
  - Tool: Playwright + curl + app smoke scripts
  - Steps:
    1. Chạy happy-path end-to-end demand->delivery.
    2. Chạy 3 negative path: isolation fail, inspection fail, return flow.
    3. Lưu screenshot/response logs theo scenario.
  - Expected Result: 100% scenario pass, negative path fail đúng kỳ vọng.
  - Evidence Path: `.sisyphus/evidence/final-f3-qa/`

- [x] F3b. UX Friction QA
  - Tool: manual walkthrough + step counter + optional Playwright
  - Steps:
    1. Đo click/tap budget cho planning, work log, harvest, packing, QR public.
    2. Verify role-first dashboard: user thấy action cần làm ngay sau login.
    3. Verify form không bắt nhập lại dữ liệu đã biết và lỗi validation chỉ rõ cách sửa.
  - Expected Result: Các workflow chính đạt click/tap budget hoặc có issue Major kèm remediation.
  - Evidence Path: `.sisyphus/evidence/final-f3b-ux-friction.md`

- [x] F3c. Security & Privacy Gate
  - Tool: API policy tests + manual public QR privacy check
  - Steps:
    1. Verify user farm A không đọc/ghi được dữ liệu farm B qua API/Filament action.
    2. Verify sensitive actions require correct role/scope/status and create audit log.
    3. Verify public QR response contains only whitelist fields and login/upload endpoints have rate/validation controls.
  - Expected Result: 0 cross-farm data leak, 0 sensitive action bypass, 0 public QR forbidden fields.
  - Evidence Path: `.sisyphus/evidence/final-f3c-security-privacy.md`

- [x] F3d. Offline Sync Contract Gate
  - Tool: API tests/curl with idempotency keys
  - Steps:
    1. Submit duplicate work log/photo upload with same idempotency key/client_uuid.
    2. Simulate conflict by changing task status before retry.
    3. Verify response exposes sync/conflict state enough for Expo UI.
  - Expected Result: No duplicate logs/media; conflict returns current state + next allowed actions.
  - Evidence Path: `.sisyphus/evidence/final-f3d-offline-sync.md`

- [x] F4. Scope Fidelity Check (deep)
  - Tool: deep review + diff against plan scope
  - Steps:
    1. So sánh deliverables thực tế với TODO 1-18.
    2. Đánh dấu mọi mục out-of-scope nếu có.
    3. Chốt trạng thái “scope clean” trước nghiệm thu.
  - Expected Result: không thiếu hạng mục committed, không creep ngoài scope.
  - Evidence Path: `.sisyphus/evidence/final-f4-scope-fidelity.md`

- [x] F4b. Business Architecture Review
  - Tool: BRD + business guardrail checklist + API/DB review
  - Steps:
    1. Verify planning response có season/climate assumptions, shortage warnings, unit conversion, rounding.
    2. Verify allocation xét soil history/crop rotation và batch hỗ trợ multi-harvest lots.
    3. Verify grade/reject taxonomy, override governance, material usage snapshot, customer/contract baseline, return feedback loop.
  - Expected Result: Không còn lỗ hổng nghiệp vụ blocker trong MVP-0/MVP-1; các mục MVP-2 có owner và phase rõ.
  - Evidence Path: `.sisyphus/evidence/final-f4b-business-architecture.md`

- [x] F5. Release Readiness Gate
  - Tool: QA sign-off checklist
  - Steps:
    1. Tổng hợp blocker/critical/major còn mở.
    2. Xác nhận invariant bắt buộc và QR privacy đều có evidence pass.
    3. Xác nhận rollback/backup note, known issues, và UAT sign-off đã sẵn sàng.
  - Expected Result: 0 blocker, 0 critical, major còn lại có owner + accepted risk; đủ điều kiện release MVP.
  - Evidence Path: `.sisyphus/evidence/final-f5-release-readiness.md`

- [x] F6. Operations & Performance Gate
  - Tool: deployment checklist + health checks + lightweight perf smoke
  - Steps:
    1. Verify `.env.example`, queue/scheduler/storage config, health checks, and backup/restore note.
    2. Run critical query/perf smoke for task list, planning calculate, and QR public.
    3. Verify job/upload/domain error logging includes request_id or trace context.
  - Expected Result: operational checklist complete; critical flows meet performance budget or have accepted remediation.
  - Evidence Path: `.sisyphus/evidence/final-f6-ops-performance.md`

---

## Commit Strategy
- Commit theo từng wave, mỗi wave gồm migration + tests + API/resource liên quan.

## Success Criteria
- Tất cả acceptance criteria wave-level pass
- MVP-1 end-to-end demand -> QR pass trên Filament/API với evidence đầy đủ
- Web/mobile/delivery/return pass khi bước sang MVP-2/MVP-3 tương ứng
- Bằng chứng đầy đủ trong `.sisyphus/evidence/`
