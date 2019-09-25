<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Twitter Collector</title>
	<!-- Favicon -->
	<link href="favicon.png" rel="icon" type="image/png">
	<!-- Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
	<!-- Icons -->
	<link href="assets/nucleo/css/nucleo.css" rel="stylesheet">
	<link href="assets/fontawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
	<!-- Argon CSS -->
	<link rel="stylesheet" href="assets/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
	<link type="text/css" href="assets/argon/argon.css" rel="stylesheet">
</head>

<body>
	<!-- Main content -->
	<div class="main-content">
		<!-- Top navbar -->
		<nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
			<div class="container-fluid">
				<!-- Brand -->
				<a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="./index.html">Twitter
					Collector</a>
			</div>
		</nav>
		<!-- Header -->
		<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
			<div class="container-fluid">
				<div class="header-body">
					<!-- Card stats -->
					<div class="row">
						<div class="col-md-8 mx-auto">
							<div class="card card-stats mb-4 mb-xl-0">
								<div class="card-body">
									<form action="" method="get" id="search">
										<div class="row">										
											<div class="col-md-12">
												<h5 class="card-title text-uppercase text-muted mb-0">Search Parameters</h5>
												<div class="row mt-3">
													<div class="col-md-12">
														<div class="form-group">
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
																</div>
																<input class="form-control" name="query" required placeholder="Query" type="text" value="{{ old('query') }}">
															</div>
															<div class="text-center">
																<div class="mt-2">
																	<span class="custom-control d-inline custom-checkbox mt-3 mx-1">
																		<input class="custom-control-input" id="disableCache" {{ old('disableCache') ? 'checked' : null }} name="disableCache" type="checkbox">
																		<label class="custom-control-label" for="disableCache">Disable Caching</label>
																	</span>
																	<span class="custom-control d-inline custom-checkbox mt-3 mx-1">
																		<input class="custom-control-input" id="hideRT" {{ old('hideRT') ? 'checked' : null }} name="hideRT" type="checkbox">
																		<label class="custom-control-label" for="hideRT">Hide Retweets</label>
																	</span>
																	
																</div>
																
																<a class="mt-3 btn btn-sm btn-default" target="_blank" href="https://developer.twitter.com/en/docs/tweets/search/overview/premium#AvailableOperators">
																	Query Operators List
																</a>																	
															</div>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
																</div>
																<input class="form-control datepicker" name="fromDate" placeholder="From Date" type="text" value="{{ old('fromDate') }}">
															</div>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
																</div>
																<input class="form-control datepicker" name="toDate" placeholder="To Date" type="text" value="{{ old('toDate') }}">
															</div>
														</div>
													</div>
													<div class="col-md-4">
														<div class="form-group">
															<div class="input-group">
																<div class="input-group-prepend">
																	<span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
																</div>
																<input class="form-control" name="maxResults" placeholder="Max Results" min="10" max="100" type="number" value="{{ old('maxResults') }}">
															</div>
														</div>
													</div>											
												</div>
											</div>
											<div class="col-md-12 text-center">
												<button class="btn btn-primary" type="submit">Submit</button>
											</div>
										</div>
									</form>	
									@isset ($tweets->error)
									<div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
										<span class="alert-inner--text"><strong>Error! </strong>{!! $tweets->error->message !!}</span>
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>			
									@endisset					
									@isset ($error)
									<div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
										<span class="alert-inner--text"><strong>{{ $error['title'] }}: </strong>
											@if (is_array($error['content']))
												@foreach ($error['content'] as $e)
													<li>{{ $e }}</li>													
												@endforeach
											@else
												{{ $error['content'] }}
											@endif
										</span>
										<button type="button" class="close" data-dismiss="alert" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>			
									@endisset					
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		@if (isset($tweets) && !isset($tweets->error))
			<div class="container-fluid mt--7">
				<div class="row mt-5">
					<div class="col-xl-8 mb-5 mb-xl-0 mx-auto">
						<div class="card shadow mb-4">
							<div class="card-body border-0">
								<div class="row align-items-center">
									<div class="col">
										<h3 class="mb-0">Tweets <span class="text-muted">({{ count($tweets->results) }})</span></h3>
										@isset($cached)
											<span class="badge badge-warning">Loaded From Cache</span>
										@endisset
									</div>
									<div class="col text-right">
										<a target="_blank" href="export.php?id={{ $download . (old('hideRT') === 'on' ? '&noRT=on' : null)}}" class="btn btn-sm btn-success">Download</a>
										@isset ($tweets->next)
											<button type="submit" name="next" form="search" value="{{ $tweets->next }}" class="btn btn-sm btn-primary">Next</button>
										@endisset
									</div>
								</div>
							</div>						
						</div>
						@foreach ($tweets->results as $tweet)
							<div class="card shadow card-stats mb-2">
								<div class="card-body">
									<div class="row">
										<div class="col">
											<h5 class="card-title text-uppercase text-primary mb-0">{{'@' . $tweet->user->screen_name }}</h5>
											<span class="mt-1">
												{!! (autoLink($tweet->truncated ? $tweet->extended_tweet->full_text : $tweet->text)) !!}			
											</span>
										</div>
									</div>
									<p class="mt-3 mb-0 text-muted text-sm">
										<span class="text-nowrap">{{ $tweet->created_at }}</span><br>
										@foreach (($tweet->truncated ? $tweet->extended_tweet : $tweet)->entities->hashtags as $tag)
											<span class="badge badge-info">{{ $tag->text }}</span>
										@endforeach										
									</p>
								</div>
							</div>
						@endforeach					
					</div>
				</div>
			</div>
		@endif
	</div>
	
	<!-- Core -->
	<script src="./assets/jquery/dist/jquery.min.js"></script>
	<script src="./assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Optional JS -->
	<script src="./assets/chart.js/dist/Chart.min.js"></script>
	<script src="./assets/chart.js/dist/Chart.extension.js"></script>
	<script src="./assets/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<!-- Argon JS -->
	<script src="./assets/argon/argon.js"></script>
</body>

</html>